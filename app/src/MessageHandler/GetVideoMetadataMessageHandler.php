<?php
namespace App\MessageHandler;


use App\Entity\JavFile;
use App\Message\GetVideoMetadataMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

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

    public function __invoke(GetVideoMetadataMessage $message)
    {
        /** @var JavFile $javFile */
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());
        if(!$javFile->getInode()->getMeta()) {
            try {
                $javFile = $this->mediaProcessorService->getMetadata($javFile);
                $this->entityManager->persist($javFile);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }
}
