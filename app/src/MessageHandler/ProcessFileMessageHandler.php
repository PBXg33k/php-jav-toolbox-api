<?php

namespace App\MessageHandler;

use App\Entity\JavFile;
use Pbxg33k\MessagePack\Message\CheckVideoMessage;
use Pbxg33k\MessagePack\Message\GenerateThumbnailMessage;
use Pbxg33k\MessagePack\Message\GetVideoMetadataMessage;
use Pbxg33k\MessagePack\Message\ProcessFileMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
    }

    public function __invoke(ProcessFileMessage $message)
    {
        /** @var JavFile $javFile */
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());

        $this->messageBus->dispatch(new GetVideoMetadataMessage($javFile->getId()));
        $this->messageBus->dispatch(new CheckVideoMessage($javFile->getId()));
        $this->messageBus->dispatch(new GenerateThumbnailMessage($javFile->getId()));
    }
}
