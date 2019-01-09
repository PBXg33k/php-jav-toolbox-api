<?php
namespace App\EventSubscriber;


use App\Event\JAVTitlePreProcessedEvent;
use App\Event\VideoFileFoundEvent;
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
            VideoFileFoundEvent::NAME => 'onVideoFileFoundEvent',
        ];
    }

    public function onVideoFileFoundEvent(VideoFileFoundEvent $event)
    {
        $this->JAVProcessorService->preProcessFile($event->getFile());
    }
}
