<?php

namespace App\MessageHandler;

use App\Event\JavFileUpdatedEvent;
use App\Event\TitleUpdatedEvent;
use App\Service\JAVProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Pbxg33k\MessagePack\Message\ScanFileMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ScanFileMessageHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var JAVProcessorService
     */
    private $JAVProcessorService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        JAVProcessorService $JAVProcessorService,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->JAVProcessorService = $JAVProcessorService;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    public function __invoke(ScanFileMessage $message)
    {
        // Set event listeners
        $this->eventDispatcher->addListener(TitleUpdatedEvent::NAME, function (TitleUpdatedEvent $event) {
            $this->entityManager->merge($event->getTitle());
        });

        $this->eventDispatcher->addListener(JavFileUpdatedEvent::NAME, function (JavFileUpdatedEvent $event) {
            $this->entityManager->merge($event->getJavFile());
            $this->entityManager->flush();
        });

        $this->JAVProcessorService->preProcessFile(new \SplFileInfo($message->getFile()));
    }
}
