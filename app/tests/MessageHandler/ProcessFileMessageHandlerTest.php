<?php

namespace App\Tests\MessageHandler;

use App\Entity\Inode;
use App\MessageHandler\ProcessFileMessageHandler;
use App\Repository\JavFileRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Entity\JavFile;
use Pbxg33k\MessagePack\Message\CheckVideoMessage;
use Pbxg33k\MessagePack\Message\GenerateThumbnailMessage;
use Pbxg33k\MessagePack\Message\GetVideoMetadataMessage;
use Pbxg33k\MessagePack\Message\ProcessFileMessage;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
    public function willDispatchAllMessages()
    {
        $message = new ProcessFileMessage('test');
        $inode   = (new Inode())->setMeta(false);
        $javFile = (new JavFile())->setPath('test')->setInode($inode);

        $repoMock = $this->createMock(JavFileRepository::class);

        $repoMock->expects($this->once())
            ->method('findOneByPath')
            ->with('test')
            ->willReturn($javFile);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(JavFile::class)
            ->willReturn($repoMock);

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
