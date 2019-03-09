<?php
namespace App\Tests\Service;

use App\Entity\Inode;
use App\Entity\JavFile;
use App\Service\JAVThumbsService;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JAVThumbsServiceTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $rootFs;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var string
     */
    private $configPath = __DIR__.'/../../config/mt.json' ;

    /**
     * @var JAVThumbsService
     */
    private $service;

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->rootFs = vfsStream::setup('testDir');

        $this->service = new JAVThumbsService(
            $this->logger,
            $this->configPath,
            $this->rootFs->url()
        );
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function willThrowExceptionIfInvalidPathIsGivenToConstructor()
    {
        $this->service = new JAVThumbsService(
            $this->logger,
            $this->configPath,
            sprintf("/nonexisting/path/%s", md5(mt_rand(0,999)))
        );
    }

    public function testConfigPathGetter()
    {
        $this->assertSame($this->configPath, $this->service->getMtConfigPath());
    }

    public function testSettersGetters()
    {
        $configPath = 'blaat';
        $this->assertSame($configPath, $this->service->setMtConfigPath($configPath)->getMtConfigPath());
        $this->assertSame($this->rootFs->url(), $this->service->setJavToolboxMediaThumbDirectory($this->rootFs->url())->getJavToolboxMediaThumbDirectory());
    }

    /**
     * @test
     */
    public function willRenameThumbnailFromFilenameToInode()
    {
        $inodeId  = 123;

        $filename = 'sintel_trailer-720p';
        $javFile = (new JavFile())
            ->setInode((new Inode())->setId($inodeId))
            ->setPath("{$this->rootFs->url()}/{$filename}.mp4")
            ->setFilename($filename);

        // Setup VFS
        vfsStream::newFile("{$filename}.jpg")
            ->withContent(LargeFileContent::withMegabytes(2))
            ->at($this->rootFs);

        $this->logger->expects($this->once())
            ->method('debug');

        $thumbnail = $this->service->getThumbnail($javFile);

        $this->assertInstanceOf(\SplFileInfo::class, $thumbnail);

        $this->assertFalse($this->rootFs->hasChild("{$filename}.jpg"));
        $this->assertTrue($this->rootFs->hasChild("{$inodeId}.jpg"));
    }

    /**
     * @test
     */
    public function willRemoveAlreadyRenamedThumbnail()
    {
        $inodeId  = 123;

        $filename = 'sintel_trailer-720p';
        $javFile = (new JavFile())
            ->setInode((new Inode())->setId($inodeId))
            ->setPath("{$this->rootFs->url()}/{$filename}.mp4")
            ->setFilename($filename);

        // Setup VFS
        vfsStream::newFile("{$filename}.jpg")
            ->withContent(LargeFileContent::withMegabytes(2))
            ->at($this->rootFs);
        vfsStream::newFile("{$inodeId}.jpg")
            ->withContent(LargeFileContent::withMegabytes(2))
            ->at($this->rootFs);

        $this->logger->expects($this->once())
            ->method('debug');

        // Assert before state
        $this->assertTrue($this->rootFs->hasChild("{$filename}.jpg"));
        $this->assertTrue($this->rootFs->hasChild("{$inodeId}.jpg"));

        $thumbnail = $this->service->getThumbnail($javFile);

        // Assert after state
        $this->assertInstanceOf(\SplFileInfo::class, $thumbnail);
        $this->assertFalse($this->rootFs->hasChild("{$filename}.jpg"));
        $this->assertTrue($this->rootFs->hasChild("{$inodeId}.jpg"));
    }

    /**
     * @test
     */
    public function willNotGenerateAlreadyGeneratedThumbs()
    {
        $inodeId  = 123;

        $filename = 'sintel_trailer-720p';
        $javFile = (new JavFile())
            ->setInode((new Inode())->setId($inodeId))
            ->setPath("{$this->rootFs->url()}/{$filename}.mp4")
            ->setFilename($filename);

        // Setup VFS
        vfsStream::newFile("{$filename}.jpg")
            ->withContent(LargeFileContent::withMegabytes(2))
            ->at($this->rootFs);

        $this->logger->expects($this->exactly(2))
            ->method('debug');

        $this->assertFalse($this->service->generateThumbs($javFile));

        $this->assertFalse($this->rootFs->hasChild("{$filename}.jpg"));
        $this->assertTrue($this->rootFs->hasChild("{$inodeId}.jpg"));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Path is not a file
     */
    public function willThrowExceptionIfFileDoesNotExistWhenGeneratingThumb()
    {
        $inodeId  = 123;

        $filename = 'sintel_trailer-720p';
        $javFile = (new JavFile())
            ->setInode((new Inode())->setId($inodeId))
            ->setPath("{$this->rootFs->url()}/{$filename}.mp4")
            ->setFilename($filename);

        $this->logger->expects($this->once())
            ->method('error');

        $this->service->generateThumbs($javFile);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage File is not readable
     */
    public function willThrowExceptionIfFileIsNotReadableWhenGeneratingThumb()
    {
        $inodeId  = 123;

        $filename = 'sintel_trailer-720p';
        $javFile = (new JavFile())
            ->setInode((new Inode())->setId($inodeId))
            ->setPath("{$this->rootFs->url()}/{$filename}.mp4")
            ->setFilename($filename);

        // Setup VFS
        vfsStream::newFile("{$filename}.mp4")
            ->withContent(LargeFileContent::withMegabytes(2))
            ->chmod(0000)
            ->at($this->rootFs);

        $this->logger->expects($this->once())
            ->method('error');

        $this->assertFalse($this->service->generateThumbs($javFile));

        $this->assertFalse($this->rootFs->hasChild("{$filename}.jpg"));
        $this->assertTrue($this->rootFs->hasChild("{$inodeId}.jpg"));
    }

    /**
     * Inject a real video file into vfs for testing mt command
     *
     * @param string $filename
     * @return \org\bovigo\vfs\vfsStreamContent|\org\bovigo\vfs\vfsStreamFile
     */
    private function createVideoTestFile(string $filename)
    {
        return vfsStream::newFile($filename)
            ->withContent(file_get_contents(__DIR__.'/../sintel_trailer-720p.mp4'))
            ->at($this->rootFs);
    }
}
