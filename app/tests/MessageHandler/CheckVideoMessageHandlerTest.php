<?php
namespace App\Tests\MessageHandler;

use App\Entity\Inode;
use App\Repository\JavFileRepository;
use Pbxg33k\MessagePack\Message\CalculateFileHashesMessage;
use Pbxg33k\MessagePack\Message\CheckVideoMessage;
use Pbxg33k\MessagePack\Message\GenerateThumbnailMessage;
use App\MessageHandler\CheckVideoMessageHandler;
use App\Service\MediaProcessorService;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Entity\JavFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CheckVideoMessageHandlerTest extends TestCase
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
     * @var JavFileRepository
     */
    private $javFileRepository;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var MessageBusInterface|MockObject
     */
    private $messageBus;

    /**
     * @var CheckVideoMessageHandler
     */
    private $handler;

    /**
     * @var vfsStreamFile
     */
    private $dummyFile;

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

        $this->javFileRepository = $this->createMock(JavFileRepository::class);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(JavFile::class)
            ->willReturn($this->javFileRepository);

        $this->handler = new CheckVideoMessageHandler(
            $this->mediaProcessorService,
            $this->entityManager,
            $this->logger,
            $this->messageBus
        );


        $root = vfsStream::setup();
        $this->dummyFile = vfsStream::newFile('ABC-123.mp4')->at($root);
    }

    /**
     * @test
     */
    public function willCheckNewVideoFile()
    {
        $callback = function($type, $buffer) {};
        $message = new CheckVideoMessage('test', $callback);

        $inode    = (new Inode());
        $javFile  = (new JavFile())->setId(1)->setInode($inode)->setpath($this->dummyFile->url());
        $javFile2 = clone $javFile;
        $javFile2->setInode(clone $javFile->getInode());

        $this->javFileRepository->expects($this->once())
            ->method('findOneByPath')
            ->with('test')
            ->willReturn($javFile);

        $this->mediaProcessorService->expects($this->once())
            ->method('checkHealth')
            ->with(
                $javFile,
                true,
                function($subject) {
                    return is_callable($subject);
                }
            )
            ->willReturn($javFile2->setInode(($javFile2->getInode()->setChecked(true)->setConsistent(true))));

        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive($javFile2, $inode);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->messageBus->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                self::isInstanceOf(GenerateThumbnailMessage::class),
                self::isInstanceOf(CalculateFileHashesMessage::class)
            )
            ->willReturn(new Envelope($message));

        $handler = $this->handler;
        $handler($message);
    }

    /**
     * @test
     */
    public function willNotDispatchMessagesIfVideoNotConsistent()
    {
        $callback = function($type, $buffer) {};
        $message = new CheckVideoMessage('test', $callback);

        $inode    = (new Inode());
        $javFile  = (new JavFile())->setId(1)->setInode($inode)->setPath($this->dummyFile->url());
        $javFile2 = clone $javFile;
        $javFile2->setInode(clone $javFile->getInode());

        $this->javFileRepository->expects($this->once())
            ->method('findOneByPath')
            ->with('test')
            ->willReturn($javFile);

        $this->mediaProcessorService->expects($this->once())
            ->method('checkHealth')
            ->with(
                $javFile,
                true,
                function($subject) {
                    return is_callable($subject);
                }
            )
            ->willReturn($javFile2->setInode(($javFile2->getInode()->setChecked(true)->setConsistent(false))));

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $handler = $this->handler;
        $handler($message);
    }

    /**
     * @test
     */
    public function willNotRecheckFileButDispatchMessagesIfAlreadyCheckedAndConsistent()
    {

        $callback = function($type, $buffer) {};
        $message = new CheckVideoMessage('test', $callback);

        $inode    = (new Inode())->setConsistent(true)->setChecked(true);
        $javFile  = (new JavFile())->setId(1)->setInode($inode)->setPath($this->dummyFile->url());

        $this->javFileRepository->expects($this->once())
            ->method('findOneByPath')
            ->with('test')
            ->willReturn($javFile);

        $this->mediaProcessorService->expects($this->never())
            ->method('checkHealth');

        $this->messageBus->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                self::isInstanceOf(GenerateThumbnailMessage::class),
                self::isInstanceOf(CalculateFileHashesMessage::class)
            )
            ->willReturn(new Envelope($message));

        $handler = $this->handler;
        $handler($message);
    }

    /**
     * @test
     */
    public function willNotRecheckFileAndDispatchMessagesIfAlreadyCheckedAndNotConsistent()
    {

        $callback = function($type, $buffer) {};
        $message = new CheckVideoMessage('test', $callback);

        $inode    = (new Inode())->setConsistent(false)->setChecked(true);
        $javFile  = (new JavFile())->setPath('test')->setInode($inode)->setPath($this->dummyFile->url());

        $this->javFileRepository->expects($this->once())
            ->method('findOneByPath')
            ->with('test')
            ->willReturn($javFile);

        $this->mediaProcessorService->expects($this->never())
            ->method('checkHealth');

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $handler = $this->handler;
        $handler($message);
    }
}
