<?php
namespace App\Tests\MessageHandler;

use App\Entity\Inode;
use App\Message\CalculateFileHashesMessage;
use App\MessageHandler\CalculateFileHashesMessageHandler;
use App\Service\FileHandleService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Entity\JavFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CalculateFileHashesMessageHandlerTest extends TestCase
{
    /**
     * @var FileHandleService|MockObject
     */
    private $fileHandleService;

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
     * @var CalculateFileHashesMessageHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->fileHandleService = $this->getMockBuilder(FileHandleService::class)
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

        $this->handler = new CalculateFileHashesMessageHandler(
            $this->entityManager,
            $this->logger,
            $this->messageBus,
            $this->fileHandleService
        );
    }

    /**
     * @test
     */
    public function willDispatchAllMessages()
    {
        $message = new CalculateFileHashesMessage(1, CalculateFileHashesMessage::HASH_MD5 | CalculateFileHashesMessage::HASH_SHA1 | CalculateFileHashesMessage::HASH_SHA512 | CalculateFileHashesMessage::HASH_XXHASH);

        $hashResults = [
            'md5'    => 'abc',
            'sha1'   => 'def',
            'sha512' => '012',
            'xxhsum' => '345'
        ];
        $inode   = (new Inode());
        $javFile = (new JavFile())->setInode($inode);

        $this->entityManager->expects($this->once())
            ->method('find')
            ->willReturn($javFile);

        $this->fileHandleService->expects($this->once())
            ->method('calculateMd5Hash')
            ->with($javFile)
            ->willReturn($javFile->setInode($javFile->getInode()->setMd5($hashResults['md5'])));

        $this->fileHandleService->expects($this->once())
            ->method('calculateSha1Hash')
            ->with($javFile)
            ->willReturn($javFile->setInode($javFile->getInode()->setMd5($hashResults['sha1'])));

        $this->fileHandleService->expects($this->once())
            ->method('calculateSha512Hash')
            ->with($javFile)
            ->willReturn($javFile->setInode($javFile->getInode()->setMd5($hashResults['sha512'])));

        $this->fileHandleService->expects($this->once())
            ->method('calculateXxhash')
            ->with($javFile)
            ->willReturn($javFile->setInode($javFile->getInode()->setMd5($hashResults['xxhsum'])));

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($javFile);

        $this->entityManager->expects($this->once())
            ->method('flush');


        $handler = $this->handler;
        $handler($message);
    }
}
