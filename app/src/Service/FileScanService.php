<?php
namespace App\Service;

use App\Event\VideoFileFoundEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

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
     * @var LoggerInterace
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

    private $rootPath;

    private $extensionRegex;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->setLogger($logger);
        $this->dispatcher = $dispatcher;
        $this->files = new ArrayCollection();
        $this->filesystem = new Filesystem();
        $this->extensionRegex = sprintf('/.%s$/i', implode('|.', $this->videoExtensions));
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
                $iv->isFile() &&
                \in_array($iv->getExtension(), $this->videoExtensions, false) &&
                $iv->getSize() >= 300000000
            ) {
                $finfo = new SplFileInfo(
                    $iv->getPathname(),
                    ltrim($iv->getPath(), $this->rootPath),
                    ltrim($iv->getPathname(), $this->rootPath)
                );

                try {
                    $this->processFile($finfo);
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }
            }
        }
    }

    protected function processFile(SplFileInfo $file) {
        if(JAVProcessorService::filenameContainsID($file->getFilename())) {
            $this->logger->debug(sprintf('file found: %s', $file->getPathname()));
            $this->dispatcher->dispatch(VideoFileFoundEvent::NAME, new VideoFileFoundEvent($file));
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
