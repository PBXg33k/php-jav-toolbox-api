<?php
namespace App\Service;

use App\Entity\Inode;
use App\Entity\Title;
use App\Entity\JavFile;
use App\Exception\PreProcessFileException;
use App\Message\CheckVideoMessage;
use App\Message\GetVideoMetadataMessage;
use App\Message\ProcessFileMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Service\FilenameParser;

/**
 * Class JAVProcessorService
 *
 * Service which processes videofiles which could contain JAV.
 *
 * It processes filenames and tries to extract JAV Titles.
 * Calculate hashes to create a fingerprint
 *
 * @package App\Service
 */
class JAVProcessorService
{
    static $blacklistnames = [
        'hentaikuindo'
    ];

    const LOG_BLACKLIST_NAME  = 'Filename contains blacklisted string';
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
     * @var string
     */
    private $mtConfigPath;

    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        MediaProcessorService $mediaProcessorService,
        MessageBusInterface $messageBus,
        $javToolboxMediaThumbDirectory,
        $javToolboxMtConfigPath
    )
    {
        $this->logger                   = $logger;
        $this->dispatcher               = $dispatcher;
        $this->entityManager            = $entityManager;
        $this->mediaProcessorService    = $mediaProcessorService;
        $this->messageBus               = $messageBus;

        $this->titles                   = new ArrayCollection();

        $this->thumbnailDirectory       = $javToolboxMediaThumbDirectory;
        $this->mtConfigPath             = $javToolboxMtConfigPath;
    }

    public function processFile(JavFile $file)
    {
        $this->logger->info('PROCESSING FILE '. $file->getFilename());

        $this->logger->debug('Dispatching message',[
            'message' => 'ProcessFileMessage',
            'id'      => $file->getId(),
            'path'    => $file->getPath()
        ]);

        $this->messageBus->dispatch(new ProcessFileMessage($file->getId()));
    }

    public function getMetadata(JavFile $file, bool $refresh = true) {
        $this->logger->notice('Dispatching message',[
            'path'    => $file->getPath(),
            'refresh' => $refresh
        ]);

        $this->messageBus->dispatch(new GetVideoMetadataMessage($file->getId()));
    }

    public function checkJAVFilesConsistency(Title $title, bool $force = false)
    {
        /** @var JavFile $javFile */
        foreach($title->getFiles() as $javFile)
        {
            if(!$javFile->getInode()->isChecked()) {
                $this->checkVideoConsistency($javFile, true, $force);
            }
        }
    }

    public function checkVideoConsistency(JavFile $file, bool $strict = true, bool $force = false)
    {
        if(!$this->entityManager->contains($file)) {
            $this->entityManager->persist($file);
        }

        $this->logger->notice('Dispatching message',[
            'path'   => $file->getPath(),
            'strict' => $strict,
            'force'  => $force
        ]);
        $this->messageBus->dispatch(new CheckVideoMessage($file->getId()));
    }

    /**
     * @param SplFileInfo $file
     *
     * @todo lower complexity. This is a mess
     */
    public function preProcessFile(SplFileInfo $file)
    {
        /** @var \App\Entity\JavFile $javFile */
        $javFile = $this->entityManager->getRepository(JavFile::class)
            ->findOneBy([
                'path' => $file->getPathname()
            ]);

        $javTitleInfo = self::extractIDFromFilename($file->getFilename());
        /** @var Title $title */
        $title = $this->entityManager->getRepository(Title::class)
            ->findOneBy(['catalognumber' => $javTitleInfo->getCatalognumber()]);

        if(
            $javFile &&
            strcasecmp($javFile->getTitle()->getCatalognumber(), $javTitleInfo->getCatalognumber()) &&
            $javFile->getInode() &&
            $javFile->getInode()->getMeta() &&
            $javFile->getInode()->isChecked()
        ) {
            $this->logger->info('PATH ALREADY PROCESSED. CONTINUING', [
                'catalog-id' => $javFile->getTitle()->getCatalognumber(),
                'path'       => $javFile->getPath()
            ]);
            return;
        }

        try {
            if(!$javFile) {
                /** @var \App\Entity\JavFile $javFile */
                $javFile = $javTitleInfo->getFiles()->first();
                $javFile->setPath($file->getPathname());

                /** @var Inode $inode */
                $inode = $this->entityManager->getRepository(Inode::class)->find($file->getInode());

                if(!$inode) {
                    $inode = (new Inode)->setId($file->getInode());
                    $inode->setFilesize($file->getSize());
                }

                $javFile->setInode($inode);

                $this->entityManager->persist($javFile);
            }

            if(!self::shouldProcessFile($javFile, $this->logger)) {
                $this->logger->warning("JAVFILE NOT VALID. SKIPPING {$javFile->getFilename()}");
                return;
            }

            if ($title) {
                $this->logger->debug('Found existing title', [
                    'catalog-number' => $title->getCatalognumber(),
                    'filename'       => $file->getFilename()
                ]);
            } else {
                $this->logger->notice('New title', [
                    'catalog-number' => $javTitleInfo->getCatalognumber(),
                    'filename'       => $file->getFilename()
                ]);
                $title = $javTitleInfo;
                $this->entityManager->persist($title);
            }
            $title->replaceFile($javFile);
            $javFile->setTitle($title);

            $this->entityManager->merge($javFile);
            $this->entityManager->flush();

            $this->processFile($javFile);
            $this->logger->info('STORED TITLE: ' . $title->getCatalognumber());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public static function extractIDFromFilename(string $fileName): Title
    {
        return self::extractID($fileName);
    }

    public static function shouldProcessFile(JavFile $javFile, LoggerInterface $logger)
    {
        $fileName = trim(pathinfo($javFile->getPath(), PATHINFO_FILENAME));

        if(ctype_xdigit($fileName) || $fileName === 'videoplayback') {
            $logger->warning(self::LOG_UNKNOWN_JAVJACK);
            return false;
        }

        foreach(self::$blacklistnames as $blacklistname) {
            if(stripos($javFile->getFilename(), $blacklistname) !== FALSE) {
                $logger->warning(self::LOG_BLACKLIST_NAME);
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $fileName
     * @return Title
     * @throws PreProcessFileException
     */
    private static function extractID(string $fileName)//: Title
    {
        $javTitle = false;

        $matchers = [
            FilenameParser\CustomMarozParser::class,
            FilenameParser\CustomParserHjd2048::class,
            FilenameParser\ProcessedFilenameParser::class,
            FilenameParser\Level1Parser::class,
            FilenameParser\Level2Parser::class,
            FilenameParser\Level3Parser::class,
            FilenameParser\Level5Parser::class,
            FilenameParser\Hack5Parser::class,
            FilenameParser\Level6Parser::class,
            FilenameParser\Level7Parser::class,
            FilenameParser\Level10Parser::class,
            FilenameParser\Level11Parser::class,
            FilenameParser\Level40Parser::class,
            FilenameParser\CustomSkyParser::class,
            FilenameParser\Hack1Parser::class,
            FilenameParser\Hack2Parser::class,
            FilenameParser\Hack3Parser::class,
            FilenameParser\Hack4Parser::class,
            FilenameParser\Level12Parser::class,
        ];

        foreach($matchers as $matcher) {
            /** @var FilenameParser\BaseParser $matcherInstance */
            $matcherInstance = new $matcher;

            if($matcherInstance->hasMatch($fileName)) {
                $javTitle = $matcherInstance->getParts();

                $title = (new Title())
                    ->setCatalognumber(sprintf('%s-%s', $javTitle->getLabel(), $javTitle->getRelease()));

                $title->addFile(
                    (new JavFile())
                        ->setFilename($fileName)
                        ->setPart(($javTitle->getPart()) ?: 1)
                );

                return $title;
            }
        }
        throw new PreProcessFileException('Unable to detect JAV Title',1,null,$fileName);
    }

    public static function filenameContainsID(string $filename): bool
    {
        return self::extractIDFromFilename($filename) instanceof Title;
    }
}
