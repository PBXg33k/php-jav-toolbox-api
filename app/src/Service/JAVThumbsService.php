<?php
namespace App\Service;

use App\Entity\Title;
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

    public function __construct(LoggerInterface $logger, string $mtConfigPath)
    {
        $this->logger = $logger;
        $this->setMtConfigPath($mtConfigPath);
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

    public function generateThumbs(Title $title)
    {
        $this->logger->debug("Generating thumbs for title", [
            'title' => $title->getCatalognumber()
        ]);

        foreach($title->getFiles() as $file)
        {
            $process = new Process([
                "mt",
                "--config-file=\"{$this->getMtConfigPath()}\"",
                "\"{$file->getPath()}\""
            ]);
            $this->logger->debug("Running MT CMD", [
                'cmd' => $process->getCommandLine()
            ]);
            try{
                $process->mustRun();

                $this->logger->info($process->getOutput(), [
                    'cmd' => $process->getCommandLine()
                ]);
            } catch (ProcessFailedException $exception) {
                $this->logger->error($exception->getMessage(), [
                    'cmd' => $process->getCommandLine(),
                    'exception_code' => $exception->getCode()
                ]);
            }
        }
    }
}
