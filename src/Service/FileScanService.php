<?php
namespace App\Service;


use App\Event\VideoFileFoundEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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

    private $ignoredNames = [
        '.',
        '..'
    ];

    private $rootPath;

    private $extensionRegex;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->logger = $logger;
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

//        $this->scanWithFinder($directory);
//        $this->scanCustom($directory);
        $this->scanRecursiveIterator($directory);

        return $this;
    }

    protected function scanRecursiveIterator(string $path)
    {
//        $directory = new \RecursiveDirectoryIterator($path);
//        $iterator = new \RecursiveIteratorIterator($directory);
//        $regex = new \RegexIterator($iterator, $this->extensionRegex, \RecursiveRegexIterator::GET_MATCH);

        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);


        /**
         * @var $iv \SplFileInfo
         */
        foreach($items as $ik => $iv)
        {
            if(
                $iv->isFile() &&
                \in_array($iv->getExtension(), $this->videoExtensions, false) &&
                $iv->getSize() >= 500000000
            ) {

                $finfo = new SplFileInfo(
                    $iv->getPathname(),
                    ltrim($iv->getPath(), $this->rootPath),
                    ltrim($iv->getPathname(), $this->rootPath)
                );

                if (JAVProcessorService::filenameContainsID($finfo->getPathname())) {
                    $this->processFile($finfo);
                } else {
                    $this->logger->notice("NO JAV ID FOUND: {$finfo->getPathname()}");
                }
            }
        }
    }

    protected function scanCustom(string $path)
    {
        if($handle = opendir($path)) {
            $this->logger->debug("Opened handle");
            while(false !== ($entry = readdir($handle))) {
                $absPath = $path.DIRECTORY_SEPARATOR.$entry;
                if(!in_array($entry, $this->ignoredNames)) {
                    $this->logger->debug("Processing entry: {$entry}");
                    if(is_file($absPath)) {
                        $this->logger->debug("{$entry} is file");
                        if (preg_match($this->extensionRegex, $entry)) {

                            $finfo = new SplFileInfo(
                                $absPath,
                                ltrim($path, $this->rootPath . DIRECTORY_SEPARATOR),
                                ltrim($absPath, $this->rootPath . DIRECTORY_SEPARATOR)
                            );

                            if($finfo->getSize() >= 500000000) {
                                if (JAVProcessorService::filenameContainsID($entry)) {
                                    $this->processFile($finfo);
                                } else {
                                    $this->logger->notice("NO JAV ID FOUND: {$entry}");
                                }
                            }
                        }
                    } elseif(is_dir($absPath)) {
                        $this->logger->info("About to scan {$absPath}");
                        $this->scanCustom($absPath);
                    }
                }
            }

            closedir($handle);
        } else {
            $this->logger->error("Unable to open dir: {$path}");
        }
    }

    protected function scanWithFinder(string $path)
    {
        $extensionRegex = sprintf('/.%s$/', implode('|.', $this->videoExtensions));

        /** @var $files Finder */
        $files = (new Finder())
            ->files()
            ->ignoreUnreadableDirs(true)
            ->in($path)
            ->size('>= 50M')
            ->name($extensionRegex)
            ->followLinks();

        $this->logger->debug(sprintf('found %s files', $files->count()));

        foreach($files as $file) {
            var_dump($file);die();
            $this->processFile($file);
        }

        return $this;
    }

    protected function processFile(SplFileInfo $file) {
        $this->logger->debug(sprintf('file found: %s', $file->getPathname()));
        $this->dispatcher->dispatch(VideoFileFoundEvent::NAME, new VideoFileFoundEvent($file));
        $this->files->add($file);
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles(): ArrayCollection
    {
        return $this->files;
    }
}