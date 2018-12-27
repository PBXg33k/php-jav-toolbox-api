<?php
namespace App\Service;

use App\Entity\JavFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

class FileHandleService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        LoggerInterface $logger,
        MessageBusInterface $messageBus
    ) {
        $this->logger     = $logger;
        $this->messageBus = $messageBus;
    }

    public function calculateMd5Hash(JavFile $javFile)
    {
        if($javFile->getInode()->getMd5()) return $javFile;
        $javFile->getInode()->setMd5($this->runHashCommand([
            'md5sum',
            $javFile->getPath()
        ]));

        return $javFile;
    }

    public function calculateSha1Hash(JavFile $javFile)
    {
        if($javFile->getInode()->getSha1()) return $javFile;
        $javFile->getInode()->setSha1($this->runHashCommand([
            'sha1sum',
            $javFile->getPath()
        ]));

        return $javFile;
    }

    public function calculateSha512Hash(JavFile $javFile)
    {
        if($javFile->getInode()->getSha512()) return $javFile;
        $javFile->getInode()->setSha512($this->runHashCommand([
            'sha512sum',
            $javFile->getPath()
        ]));

        return $javFile;
    }

    public function calculateXxhash(JavFile $javFile)
    {
        if($javFile->getInode()->getXxhash()) return $javFile;
        $javFile->getInode()->setXxhash($this->runHashCommand([
            'xxhsum',
            $javFile->getPath()
        ]));

        return $javFile;
    }

    private function runHashCommand(array $cmd) :  string
    {
        $process = new Process($cmd);
        // Increase timeout for large files (default 60 sec)
        $process->setTimeout(3600);
        $process->mustRun(function ($type, $buffer) {
            $this->logger->debug('CMD OUTPUT', [
                'type'   => $type,
                'output' => $buffer
            ]);
        });

        $hashOutput = $process->getOutput();
        if(preg_match('~(?<hash>[^\s]+)\s(?:.*)~', $hashOutput, $matches)) {
            return trim($matches['hash']);
        }

        $this->logger->error('Error creating hash', [
            'cmd'    => $cmd,
            'output' => $hashOutput,
            'process' => [
                'command'   => $process->getCommandLine(),
                'exit_code' => $process->getExitCode(),
                'output'    => $process->getOutput(),
                'erroutput' => $process->getErrorOutput()
            ]
        ]);

        throw new \Exception('Error creating hash');
    }
}
