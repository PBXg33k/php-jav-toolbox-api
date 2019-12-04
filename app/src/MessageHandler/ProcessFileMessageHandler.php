<?php

namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
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
        $this->logger->debug('ProcessFileMessageHandler triggered', [
            'path' => $message->getPath()
        ]);

        /** @var JavFileRepository $javFileRepository */
        $javFileRepository = $this->entityManager->getRepository(JavFile::class);
        $javFile = $javFileRepository->findOneByPath($message->getPath());

        $this->messageBus->dispatch(new GetVideoMetadataMessage($javFile->getPath()));
    }
}
