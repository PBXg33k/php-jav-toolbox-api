<?php
namespace App\Service;

use App\Event\DirectoryFoundEvent;
use App\Event\FileFoundEvent;
use App\Event\QualifiedVideoFileFound;
use App\Event\VideoFileFoundEvent;
use App\Message\ScanFileMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
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
        $this->filesystem           = new Filesystem();
        $this->extensionRegex       = sprintf('/.%s$/i', implode('|.', $this->videoExtensions));
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function scanDir(string $directory): FileScanService
    {
        $this->rootPath = $directory;
        $this->logger->debug('Starting scan for videofiles', [$this->rootPath]);

        $this->scanRecursiveIterator($directory);

        return $this;
    }

    protected function scanRecursiveIterator(string $path)
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
                    if ($iv->getSize() < 300000000) {
                        $this->logger->warning('File did not meet filesize requirement', [
                            'path' => $iv->getPathname(),
                            'size' => $iv->getSize(),
                            'inode' => $iv->getInode()
                        ]);
                        continue;
                    }

                    $finfo = new SplFileInfo(
                        $iv->getPathname(),
                        ltrim($iv->getPath(), $this->rootPath),
                        ltrim($iv->getPathname(), $this->rootPath)
                    );

                    try {
                        $this->processFile($finfo);
                    } catch (\Exception $exception) {
                        $this->logger->error($exception->getMessage(), [
                            'path' => $finfo->getPathname()
                        ]);
                    }
                }
            } elseif ($iv->isDir()) {
                $this->dispatcher->dispatch(DirectoryFoundEvent::NAME, new DirectoryFoundEvent($iv));
            }
        }
    }

    public function processFile(SplFileInfo $file)
    {
        if($this->javProcessorService->filenameContainsID($file)) {
            $this->logger->debug(sprintf('file found: %s', $file->getPathname()));
            $this->messageBus->dispatch(new ScanFileMessage($file));
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
