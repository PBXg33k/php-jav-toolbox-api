<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Entity\Title;
use App\Repository\JavFileRepository;
use App\Service\FileHandleService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JavCreateHashCommand extends Command
{
    protected static $defaultName = 'jav:create-hash';

    /**
     * @var FileHandleService
     */
    private $fileHandleService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**+
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FileHandleService $fileHandleService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->fileHandleService = $fileHandleService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Calculate hashes for JAV file')
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
                $this->processFile($file, $io);
                $io->success("Loaded metadata for {$file->getPath()}");
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
                    $this->processFile($file, $io);
                    $io->success("Loaded metadata for {$file->getPath()}");
                }
            }
        }
    }

    protected function processFile(JavFile $file, SymfonyStyle $output)
    {
        $file = $this->fileHandleService->calculateXxhash($file);
        if ($file->getInode()->getXxhash()) {
            $output->success('Calculated XXHASH: '.$file->getInode()->getXxhash());
        }
        $file = $this->fileHandleService->calculateMd5Hash($file);
        if ($file->getInode()->getMd5()) {
            $output->success('Calculated MD5: '.$file->getInode()->getMd5());
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        return $file;
    }
}
