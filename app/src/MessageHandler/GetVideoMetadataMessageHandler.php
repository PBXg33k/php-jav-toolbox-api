<?php

namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
use Pbxg33k\MessagePack\Message\CheckVideoMessage;
use Pbxg33k\MessagePack\Message\GetVideoMetadataMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GetVideoMetadataMessageHandler
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
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

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

    public function __invoke(GetVideoMetadataMessage $message)
    {
        /** @var JavFileRepository $repository */
        $repository = $this->entityManager->getRepository(JavFile::class);
        /** @var JavFile $javFile */
        $javFile = $repository->findOneByPath($message->getPath());
        if (!$javFile->getInode()->getMeta()) {
            try {
                $javFile = $this->mediaProcessorService->getMetadata($javFile);
                $this->entityManager->persist($javFile);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error($exception->getMessage());
                return;
            }
        }

        // Always dispatch CheckVideoMessage if no error has occured
        $this->messageBus->dispatch(new CheckVideoMessage($javFile->getPath()));
    }
}
