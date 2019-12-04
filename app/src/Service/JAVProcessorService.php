<?php

namespace App\Service;

use App\Entity\Inode;
use App\Entity\Title;
use App\Entity\JavFile;
use App\Event\JavFileUpdatedEvent;
use App\Event\TitleUpdatedEvent;
use App\Repository\JavFileRepository;
use Pbxg33k\MessagePack\Message\CheckVideoMessage;
use Pbxg33k\MessagePack\Message\GetVideoMetadataMessage;
use Pbxg33k\MessagePack\Message\ProcessFileMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SplFileInfo;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class JAVProcessorService.
 *
 * Service which processes videofiles which could contain JAV.
 *
 * It processes filenames and tries to extract JAV Titles.
 * Calculate hashes to create a fingerprint
 */
class JAVProcessorService
{
    public static $blacklistnames = [
        'hentaikuindo',
    ];

    const LOG_BLACKLIST_NAME = 'Filename contains blacklisted string';
    const LOG_UNKNOWN_JAVJACK = 'Unknown JAVJACK file detected';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ArrayCollection
     */
    private $titles;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $thumbnailDirectory;

    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    /**
     * @var JAVNameMatcherService
     */
    private $javNameMatcherService;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var JavFileRepository
     */
    private $javFileRepository;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        MediaProcessorService $mediaProcessorService,
        MessageBusInterface $messageBus,
        JAVNameMatcherService $javNameMatcherService,
        CacheItemPoolInterface $cache,
        $javToolboxMediaThumbDirectory
    ) {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->entityManager = $entityManager;
        $this->mediaProcessorService = $mediaProcessorService;
        $this->messageBus = $messageBus;
        $this->javNameMatcherService = $javNameMatcherService;
        $this->cache = $cache;

        $this->titles = new ArrayCollection();

        $this->thumbnailDirectory = $javToolboxMediaThumbDirectory;

        $this->javFileRepository = $this->entityManager->getRepository(JavFile::class);
    }

    public function processFile(JavFile $file)
    {
        $this->logger->info('PROCESSING FILE '.$file->getFilename());

        $this->logger->debug('Dispatching message', [
            'message' => 'ProcessFileMessage',
            'id' => $file->getId(),
            'path' => $file->getPath(),
        ]);

        // Check if file is persisted
        if(!$this->javFileRepository->findOneByPath($file->getPath())) {
            $this->entityManager->persist($file);
            $this->entityManager->flush();
        }

        $this->messageBus->dispatch(new ProcessFileMessage($file->getPath()));
    }

    public function getMetadata(JavFile $file, bool $refresh = true)
    {
        $this->logger->notice('Dispatching message', [
            'path' => $file->getPath(),
            'refresh' => $refresh,
        ]);

        $this->messageBus->dispatch(new GetVideoMetadataMessage($file->getPath()));
    }

    public function checkJAVFilesConsistency(Title $title, bool $force = false)
    {
        /** @var JavFile $javFile */
        foreach ($title->getFiles() as $javFile) {
            if (!$javFile->getInode()->isChecked()) {
                $this->checkVideoConsistency($javFile, true, $force);
            }
        }
    }

    public function checkVideoConsistency(JavFile $file, bool $strict = true, bool $force = false)
    {
        if (!$this->entityManager->contains($file)) {
            $this->dispatcher->dispatch(new JavFileUpdatedEvent($file));
        }

        $this->logger->notice('Dispatching message', [
            'path' => $file->getPath(),
            'strict' => $strict,
            'force' => $force,
        ]);
        $this->messageBus->dispatch(new CheckVideoMessage($file->getPath()));
    }

    private function fileExists(SplFileInfo $fileInfo)
    {
        if ($this->entityManager->getRepository(Inode::class)->exists($fileInfo->getInode())) {
            return (bool) $this->javFileRepository->findOneByFileInfo($fileInfo);
        }

        return false;
    }

    /**
     * @param SplFileInfo $file
     *
     * @todo lower complexity. This is a mess
     */
    public function preProcessFile(SplFileInfo $file)
    {
        if ($this->fileExists($file)) {
            $this->logger->debug('File already exists', [
                'path' => $file->getPathname(),
            ]);

            $this->processFile($this->javFileRepository->findOneByFileInfo($file));
        } else {
            $javTitleInfo = $this->extractIDFromFilename($file);

            try {
                /** @var \App\Entity\JavFile $javFile */
                $javFile = $javTitleInfo->getFiles()->first();

                if (!self::shouldProcessFile($javFile, $this->logger)) {
                    $this->logger->warning("JAVFILE NOT VALID. SKIPPING {$javFile->getFilename()}");

                    return;
                }

                $javFile->setPath($file->getPathname());
                /** @var Inode $inode */
                $inode = $this->entityManager->getRepository(Inode::class)->find($file->getInode());

                if (!$inode) {
                    $this->logger->debug('Inode entry not found, creating one', [
                        'path' => $file->getPathname(),
                        'inode' => $file->getInode(),
                    ]);
                    $inode = (new Inode())->setId($file->getInode());
                    $inode->setFilesize($file->getSize());
                }

                $javFile->setInode($inode);

                /** @var Title $title */
                $title = $this->entityManager
                    ->getRepository(Title::class)
                    ->findOneBy(['catalognumber' => $javTitleInfo->getCatalognumber()]);

                if (!$title) {
                    $this->logger->notice('New title', [
                        'catalog-number' => $javTitleInfo->getCatalognumber(),
                        'filename' => $file->getFilename(),
                    ]);

                    $title = $javTitleInfo;
                    $this->dispatcher->dispatch(new TitleUpdatedEvent($title));
                }
                $javFile->setTitle($title);

                $this->dispatcher->dispatch(new JavFileUpdatedEvent($javFile));

                $this->processFile($javFile);
                $this->logger->info('STORED TITLE: '.$title->getCatalognumber());
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage(), [
                    'javfile' => [
                        'catalog' => $javTitleInfo->getCatalognumber(),
                        'path' => $javFile->getPath(),
                    ],
                ]);
            }
        }
    }

    public static function shouldProcessFile(JavFile $javFile, LoggerInterface $logger)
    {
        $fileName = trim(pathinfo($javFile->getFilename(), PATHINFO_FILENAME));

        if (ctype_xdigit($fileName) || 'videoplayback' === $fileName) {
            $logger->warning(self::LOG_UNKNOWN_JAVJACK);

            return false;
        }

        foreach (self::$blacklistnames as $blacklistname) {
            if (false !== stripos($javFile->getFilename(), $blacklistname)) {
                $logger->warning(self::LOG_BLACKLIST_NAME);

                return false;
            }
        }

        return true;
    }

    public function extractIDFromFilename(SplFileInfo $fileInfo): Title
    {
        $cacheItem = $this->cache->getItem("ID_{$this->getFileKey($fileInfo)}");

        if($cacheItem->isHit()) {
            return $cacheItem->get();
        } else {
            $result = $this->javNameMatcherService->extractIDFromFileInfo($fileInfo);
            $cacheItem->set($result);
            $cacheItem->expiresAfter(84600);
            $this->cache->save($cacheItem);

            return $result;
        }

    }

    public function filenameContainsID(SplFileInfo $filename): bool
    {
        return $this->extractIDFromFilename($filename) instanceof Title;
    }

    private function getFileKey(SplFileInfo $fileInfo)
    {
        return md5("{$fileInfo->getPath()}.{$fileInfo->getSize()}");
    }
}
