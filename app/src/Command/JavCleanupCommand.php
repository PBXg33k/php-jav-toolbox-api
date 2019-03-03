<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Entity\Title;
use App\Service\MediaProcessorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JavCleanupCommand extends SectionedCommand
{
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

    public function __construct(
        EntityManagerInterface $entityManager,
        MediaProcessorService $mediaProcessorService,
        LoggerInterface $logger,
        ?string $name = null
    ) {
        $this->entityManager            = $entityManager;
        $this->mediaProcessorService    = $mediaProcessorService;
        $this->logger                   = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Will cleanup broken files')
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'Only print actions, do not execute', false)
        ;
    }

    protected function updateProgressBarWithMessage(ProgressBar $progressBar, string $message, int $steps = 1)
    {
        $progressBar->setMessage($message);
        $progressBar->advance($steps);
        $progressBar->display();
    }

    protected function initProgressBar(ProgressBar $progressBar)
    {
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - %message%');
        $progressBar->setRedrawFrequency(100);
        $progressBar->display();

        return $progressBar;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        parent::execute($input, $output);

        $this->cmdSection = $output->section();

        $dryRun = ($input->getOption('dry-run') !== false);


        if ($output instanceof ConsoleOutput) {
            /* @var ConsoleOutput $output */
            $this->overallProgressBar = $this->initProgressBar(new ProgressBar($this->stateSection, 5));
            $this->stepProgressBar = $this->initProgressBar(new ProgressBar($this->progressSection , 100));
            $this->ffmpegProgressBar = $this->initProgressBar(new ProgressBar($this->cmdSection, 100));

            $this->updateProgressBarWithMessage($this->overallProgressBar, 'Looking up inconsistent files in database');
            $brokenTitles = $this->entityManager->getRepository(Title::class)->findWithBrokenFiles();
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
                $this->checkTitleConsistencies($io, $dryRun, ...$brokenTitles);

                $this->updateProgressBarWithMessage($this->overallProgressBar, 'Complete');
            } else {
                $io->success('No broken titles found');
            }
        }
    }

    private function checkTitleConsistencies(SymfonyStyle $io, bool $dryRun, Title ...$titles)
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

                $this->logger->debug('Checking title consistency', [
                    'Title' => $brokenTitle->getCatalognumber(),
                    'File'  => $javFile->getFilename()
                ]);
                try {
                    $this->checkFileConsistency($javFile);
                    $this->entityManager->merge($javFile->getInode());
                    $this->entityManager->flush();
                    $this->logger->debug('FFMPEG CHECK PASSED');
                } catch (\Throwable $exception) {
                    if(!$dryRun) {
                        if ($io->confirm(sprintf(
                            '%s did not pass ffmpeg test. Delete file?',
                            $brokenTitle->getFiles()->first()->getFilename()
                        ), true)) {
                            $this->logger->debug('DELETING FILE', [
                                'inode' => $javFile->getInode()->getId(),
                                'path' => $javFile->getPath()
                            ]);
                            // Lookup all javfiles linked to inode
                            // Delete file from disk
                            // Remove all javfile entities linked to inode
                            // If all Success, mark success
                            $this->mediaProcessorService->delete($javFile, true, $dryRun);
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

        $this->mediaProcessorService->checkHealth(
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
                        $time = ($matches['hours'] * 3600 + $matches['minutes'] * 60 + $matches['seconds']) * 1000 + ($matches['millisec'] * 10);

                        $this->logger->debug('PERC', [
                            'time'      => $time,
                            'length'    => $length,
                            'perc'      => ($time / $length) * 100,
                            'percInt'      => round(($time / $length) * 100)
                        ]);

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
