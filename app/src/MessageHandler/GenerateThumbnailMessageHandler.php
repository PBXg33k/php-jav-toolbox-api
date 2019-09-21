<?php

namespace App\MessageHandler;

use App\Entity\JavFile;
use Pbxg33k\MessagePack\Message\GenerateThumbnailMessage;
use App\Service\JAVThumbsService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class GenerateThumbnailMessageHandler
{
    /**
     * @var JAVThumbsService
     */
    private $thumbsService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JAVThumbsService $thumbsService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->thumbsService = $thumbsService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function __invoke(GenerateThumbnailMessage $message)
    {
        /** @var JavFile $javFile */
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());
        if ($javFile->getInode()->isChecked() && $javFile->getInode()->isConsistent()) {
            $this->thumbsService->generateThumbs($javFile);
        } else {
            $this->logger->error('File conditions not met for thumbnail', [
                'path' => $javFile->getPath(),
                'checked' => $javFile->getInode()->isChecked(),
                'consistent' => $javFile->getInode()->isConsistent(),
            ]);
        }
    }
}
