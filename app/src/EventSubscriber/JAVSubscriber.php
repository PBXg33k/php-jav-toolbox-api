<?php
namespace App\EventSubscriber;


use App\Event\JAVTitlePreProcessedEvent;
use App\Event\QualifiedVideoFileFound;
use App\Service\JAVProcessorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JAVSubscriber implements EventSubscriberInterface
{
    private $JAVProcessorService;

    public function __construct(JAVProcessorService $JAVProcessorService)
    {
        $this->JAVProcessorService = $JAVProcessorService;
    }

    public static function getSubscribedEvents()
    {
        return [
            QualifiedVideoFileFound::NAME => 'onVideoFileFoundEvent',
        ];
    }

    public function onVideoFileFoundEvent(QualifiedVideoFileFound $event)
    {
        $this->JAVProcessorService->preProcessFile($event->getFile());
    }
}
