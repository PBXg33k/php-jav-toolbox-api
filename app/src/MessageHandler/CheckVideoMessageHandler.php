<?php
namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Message\CheckVideoMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CheckVideoMessageHandler implements MessageHandlerInterface
{
    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MediaProcessorService $mediaProcessorService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    )
    {
        $this->mediaProcessorService = $mediaProcessorService;
        $this->entityManager         = $entityManager;
        $this->logger                = $logger;
    }

    public function __invoke(CheckVideoMessage $message)
    {
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());
        $startTime  = time();
        $this->mediaProcessorService->checkHealth(
            $javFile,
            true,
            function ($type, $buffer) use($message, $javFile, &$startTime){
                // Force ping to DBAL to prevent time-out
                if ((time() - $startTime) >= 10) {
                    $this->entityManager->getConnection()->ping();
                    $this->logger->debug('SENT PING TO DBAL');
                    $startTime = time();
                }

                $callback = $message->getCallback();
                if(is_callable($callback)) {
                    $callback($type, $buffer);
                } else {
                    if (strpos($buffer, ' time=') !== FALSE) {
                        // Calculate/estimate progress
                        if (preg_match('~time=(?<hours>[\d]{1,2})\:(?<minutes>[\d]{2})\:(?<seconds>[\d]{2})?(?:\.(?<millisec>[\d]{0,3}))\sbitrate~', $buffer, $matches)) {
                            $time = ($matches['hours'] * 3600 + $matches['minutes'] * 60 + $matches['seconds']) * 1000 + ($matches['millisec'] * 10);

                            $this->logger->debug('Progress ' . number_format(($time / $javFile->getLength()) * 100, 2) . '%', [
                                'path' => $javFile->getPath(),
                                'length' => $javFile->getLength(),
                                'mark' => $time,
                                'perc' => number_format($time / $javFile->getLength() * 100, 2) . '%'
                            ]);
                        }
                    } else {
                        $this->logger->debug($buffer);
                    }
                }
            });
    }
}
