<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Entity\Title;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class JavCleanupCommand extends Command
{
    use SectionedCommandTrait;
    use ProgressBarCommandTrait;

    protected static $defaultName = 'jav:cleanup';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsoleSectionOutput
     */
    private $cmdSection;

    /**
     * @var ProgressBar
     */
    private $overallProgressBar;

    /**
     * @var ProgressBar
     */
    private $stepProgressBar;

    /**
     * @var ProgressBar
     */
    private $ffmpegProgressBar;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * @var bool
     */
    private $confirmed;

    public function __construct(
        EntityManagerInterface $entityManager,
        MediaProcessorService $mediaProcessorService,
        LoggerInterface $logger,
        CacheItemPoolInterface $cache,
        ?string $name = null
    ) {
        $this->entityManager            = $entityManager;
        $this->mediaProcessorService    = $mediaProcessorService;
        $this->logger                   = $logger;
        $this->cache                    = $cache;
        $this->fileSystem               = new Filesystem();

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Will cleanup broken files')
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'Only print actions, do not execute', false)
            ->addOption('yes', null, InputOption::VALUE_OPTIONAL, 'Auto agree to delete')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        parent::execute($input, $output);

        $this->cmdSection = $output->section();

        $this->dryRun    = ($input->getOption('dry-run') !== false);
        $this->confirmed = ($input->getOption('yes') !== false);

        if ($output instanceof ConsoleOutput) {
            /* @var ConsoleOutput $output */
            $this->overallProgressBar = $this->initProgressBar(new ProgressBar($this->stateSection, 5));
            $this->stepProgressBar = $this->initProgressBar(new ProgressBar($this->progressSection , 100));
            $this->ffmpegProgressBar = $this->initProgressBar(new ProgressBar($this->cmdSection, 100));

            $this->updateProgressBarWithMessage($this->overallProgressBar, 'Looking up inconsistent files in database');
            $brokenTitlesCache = $this->cache->getItem('cleanup_broken_titles');
            if($brokenTitlesCache->isHit()) {
                $brokenTitles = $brokenTitlesCache->get();
            } else {
                $brokenTitles = $this->entityManager->getRepository(Title::class)->findWithBrokenFiles();

                $brokenTitlesCache->set($brokenTitles);
                $brokenTitlesCache->expiresAfter(86400);
                $this->cache->save($brokenTitlesCache);
            }

            $brokenFileCount = 0;
            $brokenFileCount = array_sum(array_map(function(Title $title) use ($brokenFileCount) {
                return count($title->getFiles());
            }, $brokenTitles));

            if ($brokenTitles) {
                $this->stepProgressBar->setMaxSteps($brokenFileCount);

                $i = 1;
                /** @var Title $title */
                foreach ($brokenTitles as $title) {
                    $this->updateProgressBarWithMessage($this->stepProgressBar, sprintf('%d/%d Processing %s', $i, $brokenFileCount, $title->getCatalognumber()));
                    foreach ($title->getFiles() as $file) {
                        $this->stepProgressBar->advance();
                    }
                    ++$i;
                }

                // Reset step progress bar
                $this->stepProgressBar->setProgress(0);

                $this->updateProgressBarWithMessage($this->overallProgressBar, 'Checking files marked as inconsistent');
                // Do the actual checking before deleting files
                $this->checkTitleConsistencies($io, ...$brokenTitles);

                $this->updateProgressBarWithMessage($this->overallProgressBar, 'Complete');
            } else {
                $io->success('No broken titles found');
            }
        }
    }

    private function checkTitleConsistencies(SymfonyStyle $io, Title ...$titles)
    {
        $this->logger->debug('Checking title consistencies', [
            'titleCount' => count($titles)
        ]);
        $this->stepProgressBar->start();
        foreach ($titles as $brokenTitle) {
            foreach ($brokenTitle->getFiles() as $javFile) {
                $this->updateProgressBarWithMessage($this->stepProgressBar, "Processing {$javFile->getFilename()}");
                if($javFile->getInode()->isConsistent()) {
                    continue;
                }

                if(!$this->fileSystem->exists($javFile->getPath())) {
                    $this->logger->debug('File already deleted', [
                        'path' => $javFile->getPath()
                    ]);
                    $this->mediaProcessorService->delete($javFile);
                    continue;
                }

                $this->logger->debug('Checking title consistency', [
                    'Title' => $brokenTitle->getCatalognumber(),
                    'File'  => $javFile->getFilename()
                ]);
                $javFile = $this->checkFileConsistency($javFile);
                $this->logger->debug('CHECKING RESULT', [
                    'path' => $javFile->getPath(),
                    'consistent' => $javFile->getInode()->isConsistent()
                ]);

                if($javFile->getInode()->isConsistent()) {
                    $this->entityManager->merge($javFile->getInode());
                    $this->entityManager->flush();
                    $this->logger->debug('FFMPEG CHECK PASSED');
                } else {
                    if(!$this->dryRun) {
                        if(!$this->confirmed) {
                            $delete = $io->confirm(sprintf(
                                '%s did not pass ffmpeg test. Delete file?',
                                $brokenTitle->getFiles()->first()->getFilename()
                            ), true);
                        } else {
                            $delete = true;
                        }

                        if ($delete) {
                            $this->logger->debug('DELETING FILE', [
                                'inode' => $javFile->getInode()->getId(),
                                'path' => $javFile->getPath()
                            ]);
                            $this->mediaProcessorService->delete($javFile, true, $this->dryRun);
                        }
                    } else {
                        $this->logger->notice('DRYRUN: FILE DELETE TRIGGER', [
                            'path' => $javFile->getPath()
                        ]);
                    }
                }
            }
        }
    }

    private function checkFileConsistency(JavFile $file)
    {
        $this->logger->debug('CHECK', [
            'path' => $file->getPath()
        ]);
        $starttime = time();
        $this->updateProgressBarWithMessage($this->stepProgressBar, "Processing title {$file->getTitle()->getCatalognumber()}. File: {$file->getPath()}");
        $this->cmdSection->clear();

        $ffmpegBuffer = [];

        $length = $file->getInode()->getLength();

        // inode not really processed properly
        if(!$length) {
            if($this->fileSystem->exists($file->getPath())) {
                $fileInfo = new \SplFileInfo($file->getPath());
                $length = $fileInfo->getSize();
            } else {
                throw new FileNotFoundException("Could not find file {$file->getPath()}");
            }

            if(!$length) {
                return;
            }
        }

        return $this->mediaProcessorService->checkHealth(
            $file,
            true,
            function ($type, $buffer) use ($starttime, $length, $ffmpegBuffer, $file) {
                $this->logger->debug('FFMPEG BUFFER', [
                    'filename' => $file->getFilename(),
                    'buffer'   => $buffer
                ]);
                if ((time() - $starttime) >= 30) {
                    $this->entityManager->getConnection()->ping();
                }

                if (false !== strpos($buffer, ' time=')) {
                    // Calculate/estimate progress
                    if (preg_match('~time=(?<hours>[\d]{1,2})\:(?<minutes>[\d]{2})\:(?<seconds>[\d]{2})?(?:\.(?<millisec>[\d]{0,3}))\sbitrate~', $buffer, $matches)) {
                        $time = (int)($matches['hours'] * 3600 + $matches['minutes'] * 60 + $matches['seconds']) * 1000 + ($matches['millisec'] * 10);

                        if($time !== 0 && $length !== 0) {
                            $this->logger->debug('PERC', [
                                'time' => $time,
                                'length' => $length,
                                'perc' => ($time / $length) * 100,
                                'percInt' => round(($time / $length) * 100)
                            ]);
                        }

                        $this->ffmpegProgressBar->setMessage($buffer);
                        $this->ffmpegProgressBar->setProgress(round(($time / $length) * 100));
                        $this->ffmpegProgressBar->display();
                    }
                }
                $ffmpegBuffer[] = $buffer;
            },
            true
        );
    }
}
