<?php
namespace App\Service;

use App\Event\DirectoryFoundEvent;
use App\Event\FileFoundEvent;
use App\Event\VideoFileFoundEvent;
use App\Message\ScanFileMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;

class FileScanService
{
    private $videoExtensions = [
        'mp4',
        'mkv',
        'avi',
        'mpg',
        'mpeg',
        'iso',
        'wmv'
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /** @var ArrayCollection */
    private $files;

    /** @var Filesystem */
    private $filesystem;

    /**
     * @var JAVProcessorService
     */
    private $javProcessorService;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var
     */
    private $rootPath;

    /**
     * @var string
     */
    private $extensionRegex;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        JAVProcessorService $JAVProcessorService,
        MessageBusInterface $messageBus
    )
    {
        $this->setLogger($logger);
        $this->dispatcher           = $dispatcher;
        $this->javProcessorService  = $JAVProcessorService;
        $this->messageBus           = $messageBus;
        $this->files                = new ArrayCollection();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function scanDir(string $directory): self
    {
        $this->rootPath = $directory;
        $this->logger->debug('Starting scan for videofiles', [$this->rootPath]);

        $item = $this->scanRecursiveIterator($directory);

        /** @var \SplFileInfo $value */
        foreach($item as $value) {
            if ($value->getSize() < 300000000) {
                $this->logger->warning('File did not meer filesize requirements', [
                    'path'  => $value->getPathname(),
                    'size'  => $value->getSize()
                ]);
                continue;
            }

            try {
                $this->processFile($value);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return $this;
    }

    protected function scanRecursiveIterator(string $path): \Generator
    {
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        /**
         * @var $iv \SplFileInfo
         */
        foreach($items as $ik => $iv)
        {
            if(
                $iv->isFile()
            ) {
                $this->dispatcher->dispatch(FileFoundEvent::NAME, new FileFoundEvent($iv));
                if (in_array($iv->getExtension(), $this->videoExtensions, false)) {
                    $this->dispatcher->dispatch(VideoFileFoundEvent::NAME, new VideoFileFoundEvent($iv));

                    yield $iv;
                }
            } elseif ($iv->isDir()) {
                $this->dispatcher->dispatch(DirectoryFoundEvent::NAME, new DirectoryFoundEvent($iv));
            }
        }
    }

    public function processFile(\SplFileInfo $file): void
    {
        if($this->javProcessorService->filenameContainsID($file)) {
            $this->logger->debug(sprintf('file found: %s', $file->getPathname()));
            $this->messageBus->dispatch(new ScanFileMessage($file->getPathname(), ltrim($file->getPath(), $this->rootPath), ltrim($file->getPathname(), $this->rootPath)));
            $this->files->add($file);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles(): ArrayCollection
    {
        return $this->files;
    }
}
