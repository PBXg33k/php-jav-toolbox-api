<?php

namespace App\Command;

use App\Event\DirectoryFoundEvent;
use App\Event\QualifiedVideoFileFound;
use App\Event\VideoFileFoundEvent;
use App\Service\FileScanService;
use Pbxg33k\MessagePack\Message\ScanDirectoryMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ScanCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string default media path
     */
    private $javMediaFileLocation;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        string $javMediaFileLocation
    ) {
        $this->logger = $logger;
        $this->javMediaFileLocation = $javMediaFileLocation;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('jav:scan')
            ->setDescription('Scan for JAV titles locally')
            ->addArgument('path', InputArgument::OPTIONAL, 'Root path')
            ->addOption('silent', 's', InputOption::VALUE_NONE, 'Do not output anything besides errors and warnings');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getArgument('path') ?: $this->javMediaFileLocation;
        if(!is_dir($path)) {
            throw new \Exception('not a directory');
        }
        $this->messageBus->dispatch(new ScanDirectoryMessage($path));
        return;
    }
}
