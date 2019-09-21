<?php

namespace App\MessageHandler;

use App\Event\QualifiedVideoFileFound;
use Pbxg33k\MessagePack\Message\ScanFileMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ScanFileMessageHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function __invoke(ScanFileMessage $message)
    {
        $this->eventDispatcher->dispatch(QualifiedVideoFileFound::NAME, new QualifiedVideoFileFound($message->constructFileInfo()));
    }
}
