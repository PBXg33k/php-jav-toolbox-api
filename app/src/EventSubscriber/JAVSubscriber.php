<?php
namespace App\EventSubscriber;


use App\Event\JavFileUpdatedEvent;
use App\Event\JAVTitlePreProcessedEvent;
use App\Event\QualifiedVideoFileFound;
use App\Event\TitleUpdatedEvent;
use App\Service\JAVProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JAVSubscriber implements EventSubscriberInterface
{
    /**
     * @var JAVProcessorService
     */
    private $JAVProcessorService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        JAVProcessorService $JAVProcessorService,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->JAVProcessorService  = $JAVProcessorService;
        $this->entityManager        = $entityManager;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            QualifiedVideoFileFound::NAME => 'onVideoFileFoundEvent',
        ];
    }

    public function onVideoFileFoundEvent(QualifiedVideoFileFound $event)
    {
        // Set event listeners
        $this->eventDispatcher->addListener(TitleUpdatedEvent::NAME, function(TitleUpdatedEvent $event) {
            $this->entityManager->merge($event->getTitle());
        });

        $this->eventDispatcher->addListener(JavFileUpdatedEvent::NAME, function(JavFileUpdatedEvent $event) {
            $this->entityManager->merge($event->getJavFile());
            $this->entityManager->flush();
        });

        $this->JAVProcessorService->preProcessFile($event->getFile());
    }
}
