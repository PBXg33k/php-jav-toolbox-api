<?php
namespace App\Service;
use App\Event\JAVTitlePreProcessedEvent;
use App\Model\JAVTitle;
use Psr\Log\LoggerInterface;
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

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function processFile(SplFileInfo $file)
    {
        $this->logger->info('PROCESSING FILE '. $file->getPathname());
    }

    public function preProcessFile(SplFileInfo $file)
    {
        $javTitleInfo = self::extractIDFromFilename($file->getFilename());
        $javTitleInfo->setFile($file);

        if($javTitleInfo instanceof JAVTitle) {
            $parsedname = "{$javTitleInfo->getLabel()}-{$javTitleInfo->getRelease()}";
            if($javTitleInfo->getPart()) {
                $parsedname .= "-{$javTitleInfo->getPart()}";
            }
            $this->logger->info("DISPATCHING PREPROCESSEDEVENT FOR {$parsedname} | {$javTitleInfo->getFile()->getFilename()}");
            $javTitleInfo->setFile($file);
            $this->dispatcher->dispatch(JAVTitlePreProcessedEvent::NAME, new JAVTitlePreProcessedEvent($javTitleInfo));
        }
    }

    public static function extractIDFromFilename(string $fileName)
    {
        $fileName = self::cleanupFilename($fileName);

        if(preg_match("~^(?:.*?)((?<label>[a-z]{1,6})(?:[-\.]+)?(?<release>[0-9]{2,7})(?:[-_\]]+)?(?:.*?)?(?<part>\W[abcdef]|[0-9]{0,3}|cd[-_][0-9])?)(?:.{4})$~i", $fileName, $matches)) {

            $titleInfo = new JAVTitle();
            $titleInfo
                ->setFilename($fileName)
                ->setLabel($matches['label'])
                ->setRelease($matches['release']);

            if($matches['part'] !== '') {
                if(!is_numeric($matches['part'])) {
                    // Convert letter to number (a = 1, b = 2)
                    $matches['part'] = ord(strtolower($matches['part'])) - 96;
                }
                $titleInfo->setPart($matches['part']);
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
        foreach ($rightTrim as $trim) {
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