<?php
namespace App\Service;
use App\Event\DuplicateTitleFoundEvent;
use App\Event\JAVTitlePreProcessedEvent;
use App\Model\JAVFile;
use App\Model\JAVTitle;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;

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
    private $logger;

    private $dispatcher;

    /**
     * @var ArrayCollection
     */
    private $titles;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->titles = new ArrayCollection();
    }

    public function processFile(SplFileInfo $file)
    {
        $this->logger->info('PROCESSING FILE '. $file->getPathname());
    }

    public function preProcessFile(SplFileInfo $file)
    {
        $javTitleInfo = self::extractIDFromFilename($file->getFilename());

        if($javTitleInfo instanceof JAVTitle) {

            $parsedname = "{$javTitleInfo->getLabel()}-{$javTitleInfo->getRelease()}";
            $javTitleInfo->getFiles()->first()->setFile($file);

            /** @var JAVTitle $existingRecord */
            $existingRecord = $this->titles->get(self::getMapKey($javTitleInfo));

            if(!$javTitleInfo->isMultipart()) {

                if($existingRecord) {
                    $this->logger->error(
                        sprintf(
                            "DUPLICATE TITLE FOUND | %s-%s | %s : %s ",
                            $javTitleInfo->getLabel(),
                            $javTitleInfo->getRelease(),
                            $javTitleInfo->getFiles()->first()->getFilename(),
                            $existingRecord->getFiles()->first()->getFilename()
                            )
                    );

                    $this->dispatcher->dispatch(DuplicateTitleFoundEvent::NAME, new DuplicateTitleFoundEvent($existingRecord, $javTitleInfo));
                }

                $this->logger->notice("DISPATCHING PREPROCESSEDEVENT FOR {$parsedname} | {$javTitleInfo->getFiles()->first()->getFilename()}");
                $this->dispatcher->dispatch(JAVTitlePreProcessedEvent::NAME, new JAVTitlePreProcessedEvent($javTitleInfo));
            } else {

                if($existingRecord) {
                    /** @var JAVFile $file */
                    $file = $javTitleInfo->getFiles()->first();

                    try {
                        $existingRecord->setFile($file->getPart(), $file);
                    } catch (DuplicateKeyException $exception) {
                        $this->logger->error(
                            sprintf(
                                "TITLE ALREADY HAS PARTFILE | %s-%s | PART: %s | %s | %s",
                                $existingRecord->getLabel(),
                                $existingRecord->getRelease(),
                                $file->getPart(),
                                $existingRecord->getFiles()->get($file->getPart())->getFilename(),
                                $file->getFilename()
                            )
                        );
                    }

                    $javTitleInfo = $existingRecord;
                }

                if($javTitleInfo->getFiles()->first()->getPart()) {
                    $parsedname .= "-{$javTitleInfo->getFiles()->first()->getPart()}";
                }

            }
            $this->titles->set(self::getMapKey($javTitleInfo), $javTitleInfo);
        }
    }

    protected static function getMapKey(JAVTitle $title) {
        return sprintf('%s-%s', $title->getLabel(), $title->getRelease());
    }

    public static function extractIDFromFilename(string $fileName)
    {
        $fileName = self::cleanupFilename($fileName);
        return self::extractID($fileName);
    }

    private static function extractID(string $fileName)
    {
        if(preg_match("~^(?:.*?)((?<label>[a-z]{1,6})(?:[-\.]+)?(?<release>[0-9]{2,7})(?:[-_\]]+)?(?:.*?)?(?:0|hd|fhd|cd[-_]?)?(?<part>[1-9]|\W?[abcdef]|[0-9]{0,3})?).*?.{4,5}$~i", $fileName, $matches)) {

            $titleInfo = new JAVTitle();
            $titleInfo
                ->setLabel($matches['label'])
                ->setRelease($matches['release']);

            $javFile = (new JAVFile())
                ->setFilename($fileName);

            if($matches['part'] !== '') {
                if(!is_numeric($matches['part'])) {
                    // Convert letter to number (a = 1, b = 2)
                    $matches['part'] = ord(strtolower($matches['part'])) - 96;
                }
                $javFile->setPart($matches['part']);
                $titleInfo->setFile($javFile->getPart(), $javFile);

                $titleInfo->setMultipart(true);
            } else {
                $titleInfo->addFile($javFile);
            }

            return $titleInfo;
        }

        return null;
    }

    public static function cleanupFilename(string $filename) : string
    {
        if(preg_match("~^.+\.(.*)$~", $filename, $matches)) {
            $fileExtension = $matches[1];
        } else {
            throw new \Exception('Unable to extract file extension');
        }

        $filename = str_replace(".{$fileExtension}", '', $filename);

        $leftTrim = [
            'hjd2048.com-',
            'hjd2048.com'
        ];

        $rightTrim = [
            'h264',
            '1080p',
            '1080',
            '108',
            '1920',
            '108',
            'hhb',
            'fhd',
            '[hd]',
            'hd',
            'sd',
            'mp4',
            '-f'
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
            if($parsed !== null && $trim == $parsed->getRelease()) {
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
        return self::extractIDFromFilename($filename) instanceof JAVTitle;
    }
}