<?php
namespace App\Command;

use App\Service\FileScanService;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command
{
    private $fileScanService;

    private $logger;

    public function __construct(FileScanService $fileScanService, LoggerInterface $logger)
    {
        $this->fileScanService = $fileScanService;
        $this->logger = $logger;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('jav:scan')
            ->setDescription('Scan for JAV titles locally')
            ->addArgument('path', InputArgument::REQUIRED,'Root path');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        $this->logger->info("Starting scan for {$path}");
        $this->fileScanService->scanDir($path);

        $output->writeln(sprintf('Found %s eligible files', $this->fileScanService->getFiles()->count()));
    }
}