<?php
namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Message\GenerateThumbnailMessage;
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
        $this->logger        = $logger;
    }

    public function __invoke(GenerateThumbnailMessage $message)
    {
        /** @var JavFile $javFile */
        $javFile = $this->entityManager->find(JavFile::class, $message->getJavFileId());
        if ($javFile->getChecked() && $javFile->getConsistent()) {
            $this->thumbsService->generateThumbs($javFile);
        } else {
            $this->logger->error('File conditions not met for thumbnail', [
                'path'       => $javFile->getPath(),
                'checked'    => $javFile->getChecked(),
                'consistent' => $javFile->getConsistent()
            ]);
        }
    }
}
