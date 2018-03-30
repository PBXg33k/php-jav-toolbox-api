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
        $this->logger->info('PREPROCESSING FILE '. $file->getPathname());
        $javTitleInfo = self::extractIDFromFilename($file);
        $javTitleInfo->setFile($file);

        if($javTitleInfo instanceof JAVTitle) {
            $this->logger->info("DISPATCHING PREPROCESSEDEVENT FOR {$javTitleInfo->getLabel()}-{$javTitleInfo->getRelease()} | {$javTitleInfo->getFile()->getFilename()}");
            $javTitleInfo->setFile($file);
            $this->dispatcher->dispatch(JAVTitlePreProcessedEvent::NAME, new JAVTitlePreProcessedEvent($javTitleInfo));
        }
    }

    public static function extractIDFromFilename(string $fileName)
    {
        if(preg_match("~((?<label>[a-z]{1,6})(?:[-\.]+)?(?<release>[0-9]{2,7})(?:[-_\]]+)?(?<part>[abcdef]|[0-9]{0,3}|cd[-_][0-9])?)~i", $fileName, $matches)) {

            $titleInfo = new JAVTitle();
            $titleInfo
                ->setFilename($fileName)
                ->setLabel($matches['label'])
                ->setRelease($matches['release']);

            return $titleInfo;
        }

        var_dump(false, $fileName);
        return null;
    }

    public static function filenameContainsID(string $filename): bool
    {
        return self::extractIDFromFilename($filename) instanceof JAVTitle;
    }
}