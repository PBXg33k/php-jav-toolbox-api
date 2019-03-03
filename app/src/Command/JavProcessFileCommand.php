<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Entity\Title;
use App\Repository\JavFileRepository;
use App\Service\FileScanService;
use App\Service\JAVProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class JavProcessFileCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'jav:process-file';

    /**
     * @var JAVProcessorService
     */
    private $JAVProcessorService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FileScanService
     */
    private $fileScanService;

    /**+
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JAVProcessorService $JAVProcessorService,
        EntityManagerInterface $entityManager,
        FileScanService $fileScanService,
        LoggerInterface $logger
    ) {
        $this->JAVProcessorService = $JAVProcessorService;
        $this->entityManager = $entityManager;
        $this->fileScanService = $fileScanService;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Process JAV File')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'path to file or directory')
            ->addOption('catalog-id', 'c', InputOption::VALUE_OPTIONAL, 'Process all files for catalog-id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($path = $input->getOption('file')) {
            // path given scan and do magic

            /** @var JavFileRepository $fileRepository */
            $fileRepository = $this->entityManager->getRepository(JavFile::class);

            if ($file = $fileRepository->findOneBy([
                'path' => $path,
            ])) {
                $this->processFile($file);
                $io->success("Loaded metadata for {$file->getPath()}");
            } else {
                if (is_file($path)) {
                    $pathinfo = pathinfo($path);
                    $finder = Finder::create()->files()->in($pathinfo['dirname'])->name($pathinfo['basename']);

                    /** @var SplFileInfo $file */
                    foreach ($finder as $file) {
                        $this->fileScanService->processFile($file);
                    }
                }
            }
        }

        if ($catalogID = $input->getOption('catalog-id')) {
            // Catalog ID passed, lookup all associated files and do magic
            /** @var Title $title */
            if ($title = $this->entityManager->getRepository(Title::class)
                ->findOneBy([
                    'catalognumber' => strtoupper($catalogID),
                ])) {
                $files = $title->getFiles();

                foreach ($files as $file) {
                    $this->processFile($file);
                    $io->success("Loaded metadata for {$file->getPath()}");
                }
            }
        }
    }

    protected function processFile(JavFile $file)
    {
        $this->JAVProcessorService->processFile($file);
    }
}
