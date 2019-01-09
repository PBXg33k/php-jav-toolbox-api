<?php
namespace App\Tests\Service;

use App\Entity\JavFile;
use App\Entity\Title;
use App\Message\CheckVideoMessage;
use App\Message\GetVideoMetadataMessage;
use App\Message\ProcessFileMessage;
use App\Service\JAVProcessorService;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class JAVProcessorServiceTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $logger;

    /**
     * @var MockObject
     */
    protected $dispatcher;

    /**
     * @var MockObject
     */
    protected $entityManager;

    /**
     * @var JAVProcessorService
     */
    protected $service;

    /**
     * @var vfsStreamDirectory
     */
    private $mediaRoot;

    /**
     * @var MockObject
     */
    private $mediaProcessorService;

    /**
     * @var MockObject
     */
    private $messageBus;

    /**
     * @var vfsStreamDirectory
     */
    private $mediaThumbRoot;

    public function setUp()
    {
        $logger = $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mediaProcessorService = $this->mediaProcessorService = $this->getMockBuilder(MediaProcessorService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messageBus = $this->messageBus = $this->getMockBuilder(MessageBusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaRoot = vfsStream::setup('media');
        $this->mediaThumbRoot = vfsStream::setup('thumb');

        $this->service = new JAVProcessorService(
            $logger,
            $dispatcher,
            $entityManager,
            $mediaProcessorService,
            $messageBus,
            vfsStream::url('media'),
            vfsStream::url('thumb')
        );

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    /**
     * @test
     */
    public function willDispatchMessageForProcessingFile()
    {
        $javFile = (new JavFile())->setId(39);


        $this->logger->expects($this->once())
            ->method('info');

        $this->logger->expects($this->once())
            ->method('debug');

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function($subject) {
                    return $this->isInstanceOf(ProcessFileMessage::class) &&
                        $subject->getJavFileId() === 39;
                })
            )
            ->willReturn(new Envelope(new ProcessFileMessage(39)));

        $this->service->processFile($javFile);
    }

    /**
     * @test
     */
    public function willDispatchMessageForMetadata()
    {
        $javFile = (new JavFile())->setId(39);

        $this->logger->expects($this->once())
            ->method('info');

        $this->logger->expects($this->once())
            ->method('debug');

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function($subject) {
                    return $this->isInstanceOf(GetVideoMetadataMessage::class) &&
                        $subject->getJavFileId() === 39;
                })
            )
            ->willReturn(new Envelope(new GetVideoMetadataMessage(39)));


        $this->service->processFile($javFile);
    }

    /**
     * @test
     */
    public function willDispatchMessageToCheckVideoConsistency()
    {
        $javFile = (new JavFile())->setId(39);

        $this->entityManager->expects($this->once())
            ->method('contains')
            ->with($this->identicalTo($javFile))
            ->willReturn(true);

        $this->logger->expects($this->once())
            ->method('notice');

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function($subject) {
                    return $this->isInstanceOf(CheckVideoMessage::class) &&
                        $subject->getJavFileId() === 39;
                })
            )
            ->willReturn(new Envelope(new CheckVideoMessage(39)));

        $this->service->checkVideoConsistency($javFile);
    }

    /**
     * @test
     */
    public function willPersistEntityBeforeDispatchingMessageToCheckVideoConsistency()
    {
        $javFile = (new JavFile())->setId(39);

        $this->entityManager->expects($this->once())
            ->method('contains')
            ->with($this->identicalTo($javFile))
            ->willReturn(false);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturn($this->identicalTo($javFile));

        $this->logger->expects($this->once())
            ->method('notice');

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function($subject) {
                    return $this->isInstanceOf(CheckVideoMessage::class) &&
                        $subject->getJavFileId() === 39;
                })
            )
            ->willReturn(new Envelope(new CheckVideoMessage(39)));

        $this->service->checkVideoConsistency($javFile);
    } 

    /**
     * @test
     */
    public function willNotProcessInvalidJAVJackFile()
    {
        $invalidJavJackDownloads = [
            '315fbdc5be96ec692e2920bdb33b3d98',
            '5d2007905b0cc7f7b244490613eb9433',
            'videoplayback'
        ];

        foreach($invalidJavJackDownloads as $invalidJavJackDownload) {
            $this->createTestForInvalidJAVJack($invalidJavJackDownload);
        }
    }

    private function createTestForInvalidJAVJack(string $filename) {
        $javFile = (new JavFile())->setPath("/media/{$filename}.mp4");

        $this->logger->expects($this->any())
            ->method('warning')
            ->with(JAVProcessorService::LOG_UNKNOWN_JAVJACK);

        $this->assertFalse($this->service::shouldProcessFile($javFile, $this->logger));
    }

    /**
     * @test
     */
    public function willNotProcessBlacklistedFilenames()
    {
        $javFile = (new JavFile())->setFilename(join('-', ['aaa', JAVProcessorService::$blacklistnames[0], '02.avi']));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(JAVProcessorService::LOG_BLACKLIST_NAME);

        $this->assertFalse($this->service::shouldProcessFile($javFile, $this->logger));
    }

    /**
     * @test
     */
    public function extractsIDFromFilename()
    {
        $successObj = new Title();
        $filenameVariations = [
            "[ABC-123].mp4",
            "ABC-123.mp4",
            "A fancy title with ID worked in title ABC-123.wmv",
            "ABC-123 kek.mp4",
            "0302abc-123-h264.mp4",
            "[NoDRM]-abc123.mkv",
            "[Thz.la]abc-123.wmv",
            "ABC-123_720p.mp4"
        ];

        $successObj
            ->setCatalognumber('ABC-123');

        foreach($filenameVariations as $filenameVariation) {
            $successObj->addFile((new JavFile())->setFilename($filenameVariation));

            $processedFilenameResult = JAVProcessorService::extractIDFromFilename($successObj->getFiles()->first()->getFilename());

            $this->assertSame($successObj->getCatalognumber(), $processedFilenameResult->getCatalognumber());

            $this->assertSame(
                $successObj->getFiles()->first()->getFilename(),
                $processedFilenameResult->getFiles()->first()->getFilename());
        }
    }
}
