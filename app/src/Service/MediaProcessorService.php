<?php

namespace App\Service;

use App\Entity\JavFile;
use App\Exception\MediaException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mhor\MediaInfo\MediaInfo;
use Mhor\MediaInfo\Type\Video;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class MediaProcessorService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MediaInfo
     */
    private $mediaInfo;

    /**
     * @var array
     */
    private $videoInfo;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->logger        = $logger;
        $this->entityManager = $entityManager;
        $this->fileSystem    = new Filesystem();

        $this->mediaInfo     = new MediaInfo();
        $this->mediaInfo->setConfig('use_oldxml_mediainfo_output_format', true);
    }

    public function checkHealth(JavFile $javFile, bool $strict, callable $cmdCallback, bool $propogateException = false): JavFile
    {
        $this->logger->info('Checking video consistency', [
            'strict' => $strict,
            'path' => $javFile->getPath(),
            'filesize' => $javFile->getInode()->getFilesize(),
        ]);

        // command: "ffmpeg -v verbose -err_detect explode -xerror -i \"{$file->getPath()}\" -map 0:1 -f null -"
        $processArgs = [
            'ffmpeg',
            '-v',
            'verbose',
            '-err_detect',
            'explode',
            '-xerror',
            '-i',
            $javFile->getPath(),
        ];
        if (!$strict) {
            $processArgs = array_merge($processArgs, ['-map', '0:1']);
        }
        $processArgs = array_merge($processArgs, [
            '-f',
            'null',
            '-',
        ]);

        $process = new Process($processArgs);
        try {
            $process->setTimeout(3600);
            $process->mustRun($cmdCallback);

            $consistent = 0 == $process->getExitCode();

            $this->logger->debug('ffmpeg output', [
                'file' => $javFile->getPath(),
                'output' => $process->getOutput(),
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('ffmpeg failed', [
                'path' => $javFile->getPath(),
                'exception' => [
                    'message' => $exception->getMessage(),
                ],
            ]);
            $consistent = false;

            if ($propogateException) {
                $javFile->getInode()->setChecked(true)->setConsistent(false);
                throw $exception;
            }
        }

        $this->logger->info('video check completed', [
            'strict' => $strict,
            'result' => ($process->getExitCode() > 0) ? 'FAILED' : 'SUCCESS',
            'path' => $javFile->getPath(),
        ]);

        $javFile->getInode()->setChecked(true)->setConsistent($consistent);

        return $javFile;
    }

    /**
     * Uses mediainfo to retrieve file's metadata such as codec, resolution, length, etc.
     *
     * @param JavFile $javFile
     *
     * @return JavFile
     *
     * @throws \Mhor\MediaInfo\Exception\UnknownTrackTypeException
     */
    public function getMetadata(JavFile $javFile): JavFile
    {
        if ($mediaInfoContainer = $this->mediaInfo->getInfo($javFile->getPath())) {
            if ($videoInfo = $mediaInfoContainer->getVideos()) {
                $this->videoInfo = [
                    'video' => new ArrayCollection($videoInfo),
                    'general' => $mediaInfoContainer->getGeneral(),
                ];
            }

            $inode = $javFile->getInode();

            $meta = [
                'general' => $mediaInfoContainer->getGeneral()->jsonSerialize(),
                'video' => [],
                'audio' => [],
                'subtitles' => [],
                'images' => [],
                'menus' => [],
                'others' => [],
            ];

            foreach ($mediaInfoContainer->getVideos() as $video) {
                $meta['video'][] = $video->jsonSerialize();
            }

            foreach ($mediaInfoContainer->getAudios() as $audio) {
                $meta['audio'][] = $audio->jsonSerialize();
            }

            foreach ($mediaInfoContainer->getSubtitles() as $subtitle) {
                $meta['subtitles'][] = $subtitle->jsonSerialize();
            }

            foreach ($mediaInfoContainer->getImages() as $image) {
                $meta['images'][] = $image->jsonSerialize();
            }

            foreach ($mediaInfoContainer->getMenus() as $menu) {
                $meta['menus'][] = $menu->jsonSerialize();
            }

            foreach ($mediaInfoContainer->getOthers() as $other) {
                $meta['others'][] = $other->jsonSerialize();
            }

            $inode->setMeta(json_encode($meta));
        } else {
            // @todo replace exception type
            throw new MediaException('Unable to load video metadata');
        }

        /** @var Video $vinfo */
        $vinfo = $this->videoInfo['video']->first();
        if ($vinfo) {
            if ($vinfo->get('codec')) {
                $inode->setCodec($vinfo->get('codec'));
            }
            if ($vinfo->get('duration')) {
                $inode->setLength($vinfo->get('duration')->getMilliseconds());
            }
            if ($vinfo->get('bit_rate')) {
                $inode->setBitrate($vinfo->get('bit_rate')->getAbsoluteValue());
            } elseif ($vinfo->get('nominal_bit_rate')) {
                $inode->setBitrate($vinfo->get('nominal_bit_rate')->getAbsoluteValue());
            } elseif ($vinfo->get('maximum_bit_rate')) {
                $inode->setBitrate($vinfo->get('maximum_bit_rate')->getAbsoluteValue());
            }
            $inode->setWidth($vinfo->get('width')->getAbsoluteValue());
            $inode->setHeight($vinfo->get('height')->getAbsoluteValue());
            try {
                if ($frameRate = $vinfo->get('frame_rate')) {
                    $inode->setFps($frameRate->getAbsoluteValue());
                } else {
                    throw
                    new \Exception('FPS unknown');
                }
            } catch (\Exception $e) {
                if ('VFR' !== $vinfo->get('frame_rate_mode')->getShortName()) {
                    throw $e;
                }
            }
        }

        return $javFile;
    }

    public function delete(JavFile $javFile, bool $deleteAllLinked = false, bool $dryRun = false)
    {
        $files = [$javFile];

        if ($deleteAllLinked) {
            $files = $this->entityManager->getRepository(JavFile::class)->findBy([
                'inode' => $javFile->getInode()->getId(),
            ]);
        } else {
            $files = [$javFile];
        }

        $i      = 0;
        $length = count($files);
        /** @var JavFile $file */
        foreach ($files as $file) {
            if($dryRun) {
                if($i === $length - 1) {
                    $this->logger->notice('DRYRUN: LAST FILE', [
                        'path' => $file->getPath()
                    ]);
                } else {
                    $this->logger->notice('DRYRUN: Deleting file', [
                        'path' => $file->getPath()
                    ]);
                }
            } else {
                if($this->fileSystem->exists($file->getPath())) {
                    $this->fileSystem->remove($file->getPath());
                }
                $this->entityManager->remove($file);
                $this->entityManager->flush();
                $this->logger->notice("Deleted file", [
                    'path' => $file->getPath()
                ]);
            }

            $i++;
        }
    }
}
