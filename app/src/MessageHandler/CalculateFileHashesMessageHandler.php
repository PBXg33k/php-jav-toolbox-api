<?php

namespace App\MessageHandler;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
use Pbxg33k\MessagePack\Message\CalculateFileHashesMessage;
use App\Service\FileHandleService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CalculateFileHashesMessageHandler
{
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

    /**
     * @var FileHandleService
     */
    private $fileHandleService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        FileHandleService $fileHandleService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
        $this->fileHandleService = $fileHandleService;
    }

    public function __invoke(CalculateFileHashesMessage $message)
    {
        /** @var JavFileRepository $javFileRepository */
        $javFileRepository = $this->entityManager->getRepository(JavFile::class);
        $javFile = $javFileRepository->findOneByPath($message->getPath());

        if ($message->hasXxhash()) {
            $this->logger->debug('Calculating hash', [
                'method' => 'XXHASH',
                'path' => $javFile->getPath(),
            ]);
            $javFile = $this->fileHandleService->calculateXxhash($javFile);
        }

        if ($message->hasMd5()) {
            $this->logger->debug('Calculating hash', [
                'method' => 'MD5',
                'path' => $javFile->getPath(),
            ]);
            $javFile = $this->fileHandleService->calculateMd5Hash($javFile);
        }

        if ($message->hasSha1()) {
            $this->logger->debug('Calculating hash', [
                'method' => 'SHA1',
                'path' => $javFile->getPath(),
            ]);
            $javFile = $this->fileHandleService->calculateSha1Hash($javFile);
        }

        if ($message->hasSha512()) {
            $this->logger->debug('Calculating hash', [
                'method' => 'SHA512',
                'path' => $javFile->getPath(),
            ]);
            $javFile = $this->fileHandleService->calculateSha512Hash($javFile);
        }

        $this->entityManager->persist($javFile);
        $this->entityManager->flush();
    }
}
