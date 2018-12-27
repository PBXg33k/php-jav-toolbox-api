<?php
namespace App\Service;

use App\Entity\JavFile;
use Mhor\MediaInfo\MediaInfo;
use Mhor\MediaInfo\Type\Video;
use Psr\Log\LoggerInterface;
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
     * @var JAVThumbsService
     */
    private $thumbService;

    public function __construct(LoggerInterface $logger, JAVThumbsService $thumbService)
    {
        $this->logger       = $logger;
        $this->thumbService = $thumbService;

        $this->mediaInfo    = new MediaInfo();
        $this->mediaInfo->setConfig('use_oldxml_mediainfo_output_format', true);
    }

    public function checkHealth(JavFile $javFile, bool $strict, callable $cmdCallback): JavFile {
        $this->logger->info('Checking video consistency', [
            'strict'     => $strict,
            'path'       => $javFile->getPath(),
            'filesize'   => $javFile->getFilesize()
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
        if(!$strict) {
            $processArgs = array_merge($processArgs,['-map','0:1']);
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

            $consistent = $process->getExitCode() == 0;

            $this->logger->debug("ffmpeg output", [
                'file'   => $javFile->getPath(),
                'output' => $process->getOutput()
            ]);
        } catch(\Throwable $exception) {
            $this->logger->error('ffmpeg failed', [
                'path'        => $javFile->getPath(),
                'exception'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
            $consistent = false;
        }

        $this->logger->info('video check completed', [
            'strict'  => $strict,
            'result'  => ($process->getExitCode() > 0) ? 'FAILED' : 'SUCCESS',
            'path'    => $javFile->getPath(),
        ]);

        $javFile->setChecked(true);
        $javFile->setConsistent($consistent);

        return $javFile;
    }

    /**
     * Uses mediainfo to retrieve file's metadata such as codec, resolution, length, etc
     *
     * @param JavFile $javFile
     * @return JavFile
     * @throws \Mhor\MediaInfo\Exception\UnknownTrackTypeException
     */
    public function getMetadata(JavFile $javFile): JavFile {
        if ($mediaInfoContainer = $this->mediaInfo->getInfo($javFile->getPath())) {
            if ($videoInfo = $mediaInfoContainer->getVideos()) {
                $this->videoInfo = [
                    'video'   => $videoInfo,
                    'general' => $mediaInfoContainer->getGeneral(),
                ];
            }
        } else {
            // @todo replace exception type
            throw new \Exception('Unable to load video metadata');
        }

        /** @var Video $vinfo */
        $vinfo = $this->videoInfo['video'][0];
        if($vinfo) {
            $javFile->setCodec($vinfo->get('codec'));
            if($vinfo->get('duration')) {
                $javFile->setLength($vinfo->get('duration')->getMilliseconds());
            }
            if($vinfo->get('bit_rate')) {
                $javFile->setBitrate($vinfo->get('bit_rate')->getAbsoluteValue());
            } elseif($vinfo->get('nominal_bit_rate')) {
                $javFile->setBitrate($vinfo->get('nominal_bit_rate')->getAbsoluteValue());
            } else {
                $javFile->setBitrate($vinfo->get('maximum_bit_rate')->getAbsoluteValue());
            }
            $javFile->setWidth($vinfo->get('width')->getAbsoluteValue());
            $javFile->setHeight($vinfo->get('height')->getAbsoluteValue());
            try {
                if($frameRate = $vinfo->get('frame_rate')) {
                    $javFile->setFps($frameRate->getAbsoluteValue());
                } else {
                    throw
                    new \Exception("FPS unknown");
                }
            } catch (\Exception $e) {
                if($vinfo->get('frame_rate_mode')->getShortName() !== 'VFR') {
                    throw $e;
                }
            }
        }

        return $javFile;
    }

    public function generateThumbnails(JavFile $javFile): bool {
        return $this->thumbService->generateThumbs($javFile);
    }
}
