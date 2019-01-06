<?php
namespace App\Service;

use App\Entity\JavFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class JAVThumbsService
{
    /**
     * @var string
     */
    protected $mtConfigPath;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger, string $javToolboxMtConfigPath)
    {
        $this->logger = $logger;
        $this->setMtConfigPath($javToolboxMtConfigPath);
    }

    /**
     * @return string
     */
    public function getMtConfigPath(): string
    {
        return $this->mtConfigPath;
    }

    /**
     * @param string $mtConfigPath
     */
    public function setMtConfigPath(string $mtConfigPath): void
    {
        $this->mtConfigPath = $mtConfigPath;
    }

    public function generateThumbs(JavFile $javFile)
    {
        $this->logger->debug("Generating thumbs for file", [
            'path'  => $javFile->getPath()
        ]);

        $pathInfo = pathinfo($javFile->getPath());

        if(file_exists("/media/thumbs{$pathInfo['dirname']}/{$pathInfo['filename']}.jpg")) {
            $this->logger->info("thumbnail already exists", [
                'path' => $javFile->getPath()
            ]);

            return false;
        }

        $finfo = new \SplFileInfo($javFile->getPath());
        if(!$finfo->isFile()) {
            $this->logger->error('Path is not a file', [
                'path' => $javFile->getPath()
            ]);
            throw new \Exception('Path is not a file');
        }

        if(!$finfo->isReadable()) {
            $this->logger->error('File is not readable',[
                'path' => $javFile->getPath()
            ]);
            throw new \Exception('File is not readable');
        }


        $process = (new Process([
            "test",
            "-r",
            "\"{$javFile->getPath()}\""
        ]));

        if($process->getExitCode()) {
            $this->logger->error('FILE NOT READABLE BY CMD', [
                'path' => $javFile->getPath()
            ]);
            return false;
        }


        $process = (new Process([
            "mt",
            "--config-file",
            $this->getMtConfigPath(),
            $javFile->getPath()
        ]))->setTimeout(10*60);
        $this->logger->debug("Running MT CMD", [
            'cmd' => $process->getCommandLine(),
        ]);
        try{
            $process->mustRun(function($type, $buffer) {
                if(preg_match('~(?<level>[^\[]+)\[(\d+)\]\s(?<message>.*)~', $buffer, $matches)) {
                    switch($matches['level']) {
                        case 'DEBU':
                            $loglevel = 'debug';
                            break;
                        case 'INFO':
                            $loglevel = 'info';
                            break;
                        default:
                            $loglevel = 'error';
                    }

                    $this->logger->log($loglevel, $matches['level'], [
                        'process'    => [
                            'type'   => $type,
                            'buffer' => $buffer
                        ]
                    ]);
                } else {
                    $this->logger->debug('CMD OUTPUT', [
                        'buffer' => $buffer
                    ]);
                }
            });

            return $process->getExitCode() === 0;
        } catch (ProcessFailedException $exception) {
            $this->logger->error($exception->getMessage(), [
                'cmd'              => $process->getCommandLine(),
                'file'             => $javFile->getPath(),
                'exception_code'   => $exception->getCode(),
                'process_exitcode' => $exception->getProcess()->getExitCode(),
                'process_output'   => $exception->getProcess()->getOutput(),
                'proc'             => [
                    'isTty'        => $process->isTty(),
                    'isPty'        => $process->isPty(),
                    'working_dir'  => $process->getWorkingDirectory(),
                    'env'          => $process->getEnv()
                ]
            ]);
        }

        return false;
    }
}
