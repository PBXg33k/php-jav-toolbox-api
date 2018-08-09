<?php
namespace App\Service;

use App\Entity\Title;
use App\Entity\JavFile;
use App\Event\JAVTitlePreProcessedEvent;
use App\Exception\PreProcessFileException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mhor\MediaInfo\Container\MediaInfoContainer;
use Mhor\MediaInfo\MediaInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

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
     * @var MediaInfo
     */
    private $mediaInfo;

    /**
     * @var string
     */
    private $thumbnailDirectory;

    /**
     * @var string
     */
    private $mtConfigPath;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        $javToolboxMediaThumbDirectory,
        $javToolboxMtConfigPath
    )
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->titles = new ArrayCollection();
        $this->entityManager = $entityManager;
        $this->mediaInfo = new MediaInfo();

        $this->thumbnailDirectory = $javToolboxMediaThumbDirectory;
        $this->mtConfigPath = $javToolboxMtConfigPath;
    }

    public function processFile(JavFile $file)
    {
        $this->logger->info('PROCESSING FILE '. $file->getFilename());

        // Start thumbnail generation (async) first because this takes longer
//        $thumbnailProcess = new Process('')


        // Start Mediainfo command to get media data
        /** @var MediaInfoContainer $mediaInfoContainer */
        $mediaInfoContainer = $this->mediaInfo->getInfo($file->getPath());

        var_dump($mediaInfoContainer);die();
    }

    public function getJavFileMetadata(JavFile $file)
    {
        $mediaInfoContainer = $this->mediaInfo->getInfo($file->getPath());


    }

    public function checkJAVFilesConsistency(Title $title)
    {
        /** @var JavFile $javFile */
        foreach($title->getFiles() as $javFile)
        {
            $this->checkVideoConsistency($javFile);
        }
    }

    public function checkVideoConsistency(JavFile $file)
    {

    }

    public function preProcessFile(SplFileInfo $file)
    {
        /** @var \App\Entity\JavFile $existingFile */
        $existingFile = $this->entityManager->getRepository(JavFile::class)
            ->findOneBy([
                'path' => $file->getPathname()
            ]);

        $javTitleInfo = self::extractIDFromFilename($file->getFilename());

        if($existingFile && $existingFile->getTitle()->getCatalognumber() == $javTitleInfo->getCatalognumber()) {
            $this->logger->info('PATH ALREADY PROCESSED. CONTINUING: '. $existingFile->getFilename());
            return;
        }

        try {
            if($existingFile) {
                $javFile = $existingFile;
            } else {
                /** @var \App\Entity\JavFile $javFile */
                $javFile = $javTitleInfo->getFiles()->first();
                $javFile->setPath($file->getPathname());
                $javFile->setFilesize($file->getSize());
                $javFile->setInode($file->getInode());
            }

            if(!self::shouldProcessFile($javFile, $this->logger)) {
                $this->logger->notice("JAVFILE NOT VALID. SKIPPING {$javFile->getFilename()}");
                return;
            }

            // Check if Title already exists, if so append file to existing record
            /** @var Title $title */
            $title = $this->entityManager->getRepository(Title::class)
                ->findOneBy(['catalognumber' => $javTitleInfo->getCatalognumber()]);

            if ($title) {
                $this->logger->debug('Found existing title: ' . $title->getCatalognumber());
            } else {
                $this->logger->debug('New title: ' . $javTitleInfo->getCatalognumber());
                $title = $javTitleInfo;
            }
            $title->replaceFile($javFile);
            $javFile->setTitle($title);

            $this->dispatcher->dispatch(JAVTitlePreProcessedEvent::NAME, new JAVTitlePreProcessedEvent($title, $javFile));

            $this->entityManager->persist($title);
            $this->entityManager->persist($javFile);
            $this->entityManager->flush();
            $this->logger->info('STORED TITLE: ' . $title->getCatalognumber());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public static function extractIDFromFilename(string $fileName)
    {
        return self::extractID(self::cleanupFilename($fileName));
    }

    public static function shouldProcessFile(JavFile $javFile, LoggerInterface $logger)
    {
        $filenameLength = strlen($javFile->getFilename());

        if(in_array($filenameLength, [36,51,52])) {
            $logger->notice('LENGTH OF FILENAME INDICATES INCORRECT JAVJACK DL');
            return false;
        }

        foreach(self::$blacklistnames as $blacklistname) {
            if(stripos($javFile->getFilename(), $blacklistname) !== FALSE) {
                $logger->notice('FILENAME CONTAINS BLACKLISTED STRING');
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
    private static function extractID(string $fileName)
    {
        //^(?:.*?)(?:(?<label>[a-z]{1,6})(?:[-\.]+)?(?<release>[0-9]{2,7})(?:[-_\]]+)?(?:.*?)?(?:0|hd|fhd|cd?)?(?:[-_]?)?(?<part>[1-9]|\W[abcdef]|[0-9]{0,3})?\.)(\w{2,5}?)$
        if(preg_match("~^(?:.*?)((?<label>[a-z]{2,6})(?:[-\.]+)?(?<release>[0-9]{2,7})(?:[-_\]]+)?(?:.*?)?(?:0|hd|fhd|cd[-_]?)?(?<part>[1-9]|\W?[abcdef]|[0-9]{0,3})?).*?.{4,5}$~i", $fileName, $matches)) {

            $title = (new Title())
                ->setCatalognumber(sprintf('%s-%s', $matches['label'], $matches['release']));

            $filePart = 1;

            if($matches['part'] !== '') {
                if(!is_numeric($matches['part'])) {
                    // Convert letter to number (a = 1, b = 2)
                    $matches['part'] = ord(strtolower($matches['part'])) - 96;
                }

                $filePart = $matches['part'];
            }

            $title->addFile(
                (new JavFile())
                    ->setFilename($fileName)
                    ->setPart($filePart)
                    ->setProcessed(false)
                    ->setInode(1)
            );

            return $title;
        }

        throw new PreProcessFileException("Unable to extract ID {$fileName}", 1, null, $fileName);
    }

    public static function cleanupFilename(string $filename) : string
    {
        if(preg_match("~^.+\.(.*)$~", $filename, $matches)) {
            $fileExtension = $matches[1];
        } else {
            throw new \Exception('Unable to extract file extension');
        }

        $filename = str_ireplace(".{$fileExtension}", '', $filename);

        $leftTrim = [
            'hjd2048.com-',
            'hjd2048.com',
            'watch18plus_',
        ];

        $rightTrim = [
            'h264',
            '1080p',
            '1080',
            '108',
            '1920',
            '720',
            '108',
            'hhb',
            'fhd',
            '[hd]',
            'hd',
            'sd',
            'mp4',
            '-f',
            '-5'
        ];

        $filename = self::rtrim(self::ltrim($filename, $leftTrim), $rightTrim);

        return $filename.'.'.$fileExtension;
    }

    private static function ltrim(string $filename, array $leftTrim): string
    {
        foreach ($leftTrim as $trim) {
            if(stripos(strtolower($filename), $trim) === 0) {
                $filename = substr($filename, strlen($trim));
                $filename = self::ltrim($filename, $leftTrim);
            }
        }

        return $filename;
    }

    private static function rtrim(string $filename, array $rightTrim): string
    {
        // Parse filename to exclude exces filtering if filtered word is part of release
        $parsed = self::extractID("{$filename}.mp4");

        foreach ($rightTrim as $trim) {
            if($parsed !== null && $trim == explode('-', $parsed->getCatalognumber())[1]) {
                return $filename;
            }

            if(stripos($filename, $trim) === strlen($filename) - strlen($trim)) {
                $filename = substr($filename, 0, -1 * abs(strlen($trim)));
                $filename = rtrim($filename, '-');
                $filename = self::rtrim($filename, $rightTrim);
            }
        }

        return $filename;
    }

    public static function filenameContainsID(string $filename): bool
    {
        return self::extractIDFromFilename($filename) instanceof Title;
    }
}
