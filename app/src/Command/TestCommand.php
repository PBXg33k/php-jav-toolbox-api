<?php

namespace App\Command;

use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MediaProcessorService $mediaProcessorService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ?string $name = null
    ) {
        $this->mediaProcessorService = $mediaProcessorService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $process = (new Process([
            'sleep',
            60
        ]))->setTimeout(5);

        $pid = null;
        try {
            $process->start(function ($type, $buffer) use ($process) {
                $this->logger->debug("CMD", [
                    'pid' => $process->getPid()
                ]);
            });

            $pid = $process->getPid();
            $process->wait();
        } catch (ProcessTimedOutException $exception) {
            $this->logger->error($exception->getMessage(), [
                'cmd' => $process->getCommandLine(),
                'pid' => $process->getPid(),
                'exception_code' => $exception->getCode(),
                'process_exitcode' => $exception->getProcess()->getExitCode(),
                'process_output' => $exception->getProcess()->getOutput(),
                'proc' => [
                    'isTty' => $process->isTty(),
                    'isPty' => $process->isPty(),
                    'working_dir' => $process->getWorkingDirectory(),
                    'env' => $process->getEnv(),
                ],
            ]);

            if($pid !== null && posix_getpgid($pid)) {
                if (!posix_kill($pid)) {
                    throw $exception;
                }
                $this->logger->notice('KILLED PROCESS');
            }
        }
    }
}
