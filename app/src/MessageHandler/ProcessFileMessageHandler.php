<?php
namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Message\CheckVideoMessage;
use App\Message\GenerateThumbnailMessage;
use App\Message\GetVideoMetadataMessage;
use App\Message\ProcessFileMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProcessFileMessageHandler
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

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        MediaProcessorService $mediaProcessorService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        MessageBusInterface $messageBus
    ) {
        $this->mediaProcessorService = $mediaProcessorService;
        $this->entityManager         = $entityManager;
        $this->logger                = $logger;
        $this->messageBus            = $messageBus;
    }

    public function __invoke(ProcessFileMessage $message)
    {
        /** @var JavFile $javFile */
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());

        if (!$javFile->getMeta()) {
            $this->messageBus->dispatch(new GetVideoMetadataMessage($javFile->getId()));
        }

        // This event will also dispatch message to generate thumbnails if the video is valid
        $this->messageBus->dispatch(new CheckVideoMessage($javFile->getId()));

        $this->messageBus->dispatch(new GenerateThumbnailMessage($javFile->getId()));
    }
}
