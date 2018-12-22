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

        $process = new Process([
            "mt",
            "--config-file=\"{$this->getMtConfigPath()}\"",
            "\"{$javFile->getPath()}\""
        ]);
        $this->logger->debug("Running MT CMD", [
            'cmd' => $process->getCommandLine(),
        ]);
        try{
            $logger = $this->logger;
            $process->mustRun(function($type, $buffer) use ($logger) {
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

                    $logger->log($loglevel, $matches['level'], [
                        'process'    => [
                            'type'   => $type,
                            'buffer' => $buffer
                        ]
                    ]);
                }
            });

            return $process->getExitCode() === 0;
        } catch (ProcessFailedException $exception) {
            $this->logger->error($exception->getMessage(), [
                'cmd'            => $process->getCommandLine(),
                'file'           => $javFile->getPath(),
                'exception_code' => $exception->getCode()
            ]);
        }

        return false;
    }
}
