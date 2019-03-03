<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Entity\Title;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
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
        ?string $name = null
    ) {
        $this->entityManager = $entityManager;
        $this->mediaProcessorService = $mediaProcessorService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Will cleanup')
//            ->addOption('no-interaction', null, InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'Require no user interaction, implicit yes to all')
            ->addOption('dry-run', null, InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'Only print actions, do not execute')
        ;
    }

    protected function updateProgressBarWithMessage(ProgressBar $progressBar, string $message, int $steps = 1)
    {
        $progressBar->setMessage($message);
        $progressBar->advance($steps);
    }

    protected function initProgressBar(ProgressBar $progressBar)
    {
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->setRedrawFrequency(100);

        return $progressBar;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof ConsoleOutput) {
            $this->cmdSection = $output->section();
        }
        parent::execute($input, $output);

        $io = new SymfonyStyle($input, $output);

        if ($output instanceof ConsoleOutput) {
            /* @var ConsoleOutput $output */
            $this->overallProgressBar = $this->initProgressBar(new ProgressBar($output, 5));
            $this->stepProgressBar = $this->initProgressBar(new ProgressBar($output));
            $this->ffmpegProgressBar = $this->initProgressBar(new ProgressBar($output, 100));

            $this->updateProgressBarWithMessage($this->overallProgressBar, 'Looking up inconsistent files in database');
            $brokenTitles = $this->entityManager->getRepository(Title::class)->findWithBrokenFiles();
            $brokenTitlesCount = count($brokenTitles);
            $this->updateProgressBarWithMessage($this->overallProgressBar, sprintf('Found %d inconsistent files in database', $brokenTitlesCount));

            if ($brokenTitles) {
                $ioTableRows = [];

                $this->stepProgressBar->setMaxSteps($brokenTitlesCount);

                $collectiveSize = 0;
                $i = 1;
                /** @var Title $title */
                foreach ($brokenTitles as $title) {
                    $this->updateProgressBarWithMessage($this->stepProgressBar, sprintf('%d/%d Processing %s', $i, $brokenTitlesCount, $title->getCatalognumber()));
                    foreach ($title->getFiles() as $file) {
                        $ioTableRows[] = [
                            'catalog-id' => $title->getCatalognumber(),
                            'inode' => $file->getInode()->getId(),
                            'part' => $file->getPart(),
                            'filesize' => $file->getInode()->getFilesize(),
                            'filename' => $file->getFilename(),
                        ];

                        $this->stepProgressBar->advance();

                        $collectiveSize += $file->getInode()->getFilesize();
                    }
                    ++$i;
                }

                $this->updateProgressBarWithMessage($this->overallProgressBar, 'Rendering table');
                $io->table([
                    'CatalogID',
                    'Inode',
                    'Part',
                    'Filesize',
                    'Filename',
                ], $ioTableRows);

                $this->updateProgressBarWithMessage($this->overallProgressBar, 'Confirming files marked for deletion');

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
        $this->stepProgressBar->start();
        foreach ($titles as $brokenTitle) {
            foreach ($brokenTitle->getFiles() as $javFile) {
                try {
                    $this->checkFileConsistency($javFile);
                } catch (\Throwable $exception) {
                    if ($io->confirm(sprintf(
                        '%s did not pass ffmpeg test. Delete file?',
                        $brokenTitle->getFiles()->first()->getFilename()
                    ), true)) {
                        // Lookup all javfiles linked to inode
                        // Delete file from disk
                        // Remove all javfile entities linked to inode
                        // If all Success, mark success
                        $this->mediaProcessorService->delete($javFile, true);
                    }
                }
            }
        }
    }

    private function checkFileConsistency(JavFile $file)
    {
        $starttime = time();
        $this->stepProgressBar->setMessage("Processing title {$file->getTitle()->getCatalognumber()}. File: {$file->getPath()}");

        $ffmpegBuffer = [];

        $length = $file->getInode()->getLength();

        $this->mediaProcessorService->checkHealth(
            $file,
            true,
            function ($type, $buffer) use ($starttime, $length, $ffmpegBuffer) {
                if ((time() - $starttime) >= 30) {
                    $this->entityManager->getConnection()->ping();
                }

                if (false !== strpos($buffer, ' time=')) {
                    // Calculate/estimate progress
                    if (preg_match('~time=(?<hours>[\d]{1,2})\:(?<minutes>[\d]{2})\:(?<seconds>[\d]{2})?(?:\.(?<millisec>[\d]{0,3}))\sbitrate~', $buffer, $matches)) {
                        $time = ($matches['hours'] * 3600 + $matches['minutes'] * 60 + $matches['seconds']) * 1000 + ($matches['millisec'] * 10);

                        $this->ffmpegProgressBar->setProgress((int) ($time / $length) * 100);
                    }
                }

                $this->ffmpegProgressBar->setMessage($buffer);
                $ffmpegBuffer[] = $buffer;
            },
            true
        );
    }
}
