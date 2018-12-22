<?php
namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Message\GenerateThumbnailMessage;
use App\Service\JAVThumbsService;
use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(JAVThumbsService $thumbsService, EntityManagerInterface $entityManager)
    {
        $this->thumbsService = $thumbsService;
        $this->entityManager = $entityManager;
    }

    public function __invoke(GenerateThumbnailMessage $message)
    {
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());
        $this->thumbsService->generateThumbs($javFile);
    }
}
