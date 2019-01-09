<?php

namespace App\Tests\MessageHandler;

use App\Entity\Inode;
use App\MessageHandler\ProcessFileMessageHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Entity\JavFile;
use App\Message\CheckVideoMessage;
use App\Message\GenerateThumbnailMessage;
use App\Message\GetVideoMetadataMessage;
use App\Message\ProcessFileMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ProcessFileMessageHandlerTest extends TestCase
{
    /**
     * @var MediaProcessorService|MockObject
     */
    private $mediaProcessorService;

    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var MessageBusInterface|MockObject
     */
    private $messageBus;

    /**
     * @var ProcessFileMessageHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->mediaProcessorService = $this->getMockBuilder(MediaProcessorService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageBus = $this->getMockBuilder(MessageBusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new ProcessFileMessageHandler(
            $this->mediaProcessorService,
            $this->entityManager,
            $this->logger,
            $this->messageBus
        );
    }

    /**
     * @test
     */
    public function willDispatchAllMessagesOnANewFile()
    {
        $message = new ProcessFileMessage(1);
        $inode   = (new Inode())->setMeta(false);
        $javFile = (new JavFile())->setId(1)->setInode($inode);

        $this->entityManager->expects($this->once())
            ->method('find')
            ->willReturn($javFile);

        $this->messageBus->expects(self::exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                self::isInstanceOf(GetVideoMetadataMessage::class),
                self::isInstanceOf(CheckVideoMessage::class),
                self::isInstanceOf(GenerateThumbnailMessage::class)
            )->willReturn(new Envelope($message));

        $handler = $this->handler;
        $handler($message);
    }
}
