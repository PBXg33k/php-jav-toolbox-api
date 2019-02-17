<?php
namespace App\Command;

use App\Event\DirectoryFoundEvent;
use App\Event\FileFoundEvent;
use App\Event\QualifiedVideoFileFound;
use App\Event\VideoFileFoundEvent;
use App\Service\FileScanService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ScanCommand extends SectionedCommand
{
    /**
     * @var FileScanService
     */
    private $fileScanService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string default media path
     */
    private $javMediaFileLocation;

    /**
     * @var ConsoleSectionOutput
     */
    private $lastMatchSection;

    public function __construct(
        FileScanService $fileScanService,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        string $javMediaFileLocation
    )
    {
        $this->fileScanService      = $fileScanService;
        $this->logger               = $logger;
        $this->eventDispatcher     = $eventDispatcher;
        $this->javMediaFileLocation = $javMediaFileLocation;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('jav:scan')
            ->setDescription('Scan for JAV titles locally')
            ->addArgument('path', InputArgument::OPTIONAL,'Root path');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if($output instanceof ConsoleOutput) {
            $this->lastMatchSection = $output->section();
            $this->updateLastMatch('none');
        }

        $path = $input->getArgument('path') ?: $this->javMediaFileLocation;
        $this->setEventListeners($this->eventDispatcher);

        $this->updateStateMessage('Scanning');
        $this->updateProgressOutput("Starting scan for {$path}");
        $this->fileScanService->scanDir($path);

        $this->updateStateMessage('Finished');
        $this->updateProgressOutput(sprintf('Found %s eligible files', $this->fileScanService->getFiles()->count()));
    }

    private function setEventListeners(EventDispatcherInterface $eventDispatcher)
    {
        // Set event on directory.found
        $eventDispatcher->addListener(DirectoryFoundEvent::NAME, function(DirectoryFoundEvent $event) {
            $this->updateProgressOutput("Scanning directory: {$event->getFile()->getPathname()}");
        });

        $eventDispatcher->addListener(VideoFileFoundEvent::NAME, function(VideoFileFoundEvent $event) {
            $this->updateProgressOutput("Scanning file: {$event->getFile()->getPathname()}");
        });

        $eventDispatcher->addListener(QualifiedVideoFileFound::NAME, function(QualifiedVideoFileFound $event) {
            $this->updateLastMatch($event->getFile()->getPathname());
        });
    }

    private function updateLastMatch(string $path) {
        $this->writeToSection("Last match: {$path}", $this->lastMatchSection);
    }
}
