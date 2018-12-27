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
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());
        $javFile = $this->mediaProcessorService->getMetadata($javFile);
        $this->entityManager->persist($javFile);
        $this->entityManager->flush();
    }
}
