<?php

namespace App\Command;

use App\Service\FileScanService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JavRenameCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'jav:rename';

    /**
     * @var FileScanService
     */
    protected $fileScanService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(FileScanService $fileScanService, LoggerInterface $logger)
    {
        $this->fileScanService = $fileScanService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('dry-run', 'd', InputOption::VALUE_OPTIONAL, 'Do not apply changes')
            ->addOption('retain-directories', null, InputOption::VALUE_OPTIONAL, 'Retain directory structure')
            ->addOption('all', null, InputOption::VALUE_OPTIONAL, 'Rename all files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        $dryRun = (false !== $input->getOption('dry-run')) ?: false;

        if ($input->getOption('all')) {
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
