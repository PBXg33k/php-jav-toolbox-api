<?php

namespace App\Service;

use App\Entity\JavFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class JAVThumbsService
{
    /**
     * @var string
     */
    protected $mtConfigPath;

    /**
     * @var string
     */
    protected $javToolboxMediaThumbDirectory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        string $javToolboxMtConfigPath,
        string $javToolboxMediaThumbDirectory
    ) {
        $this->logger = $logger;
        $this->setJavToolboxMediaThumbDirectory($javToolboxMediaThumbDirectory);
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
     *
     * @return JAVThumbsService
     */
    public function setMtConfigPath(string $mtConfigPath): self
    {
        $this->mtConfigPath = $mtConfigPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getJavToolboxMediaThumbDirectory(): string
    {
        return $this->javToolboxMediaThumbDirectory;
    }

    /**
     * @param string $javToolboxMediaThumbDirectory
     *
     * @return JAVThumbsService
     *
     * @throws \Exception
     */
    public function setJavToolboxMediaThumbDirectory(string $javToolboxMediaThumbDirectory): JAVThumbsService
    {
        if (!is_dir($javToolboxMediaThumbDirectory)) {
            throw new \Exception('Path not a directory: '.$javToolboxMediaThumbDirectory);
        }

        $this->javToolboxMediaThumbDirectory = $javToolboxMediaThumbDirectory;

        return $this;
    }

    /**
     * @param JavFile $javFile
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @todo refactor to make more testable
     */
    public function generateThumbs(JavFile $javFile)
    {
        $this->logger->debug('Generating thumbs for file', [
            'path' => $javFile->getPath(),
        ]);

        // Check for old path
        if ($this->getThumbnail($javFile)) {
            return false;
        }

        $finfo = new \SplFileInfo($javFile->getPath());
        if (!$finfo->isFile()) {
            $this->logger->error('Path is not a file', [
                'path' => $javFile->getPath(),
            ]);
            throw new \Exception('Path is not a file');
        }

        if (!$finfo->isReadable()) {
            $this->logger->error('File is not readable', [
                'path' => $javFile->getPath(),
            ]);
            throw new \Exception('File is not readable');
        }

        $process = (new Process([
            'test',
            '-r',
            "\"{$javFile->getPath()}\"",
        ]));

        if ($process->getExitCode()) {
            $this->logger->error('FILE NOT READABLE BY CMD', [
                'path' => $javFile->getPath(),
            ]);

            return false;
        }

        $process = (new Process([
            'mt',
            '--config-file',
            $this->getMtConfigPath(),
            '--output',
            $this->getThumbPath($javFile),
            $javFile->getPath(),
        ]))->setTimeout(10 * 60);
        $this->logger->debug('Running MT CMD', [
            'cmd' => $process->getCommandLine(),
        ]);

        $pid = null;
        try {
            $process->start(function ($type, $buffer) {
                if (preg_match('~(?<level>[^\[]+)\[(\d+)\]\s(?<message>.*)~', $buffer, $matches)) {
                    switch ($matches['level']) {
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
                        'process' => [
                            'type' => $type,
                            'buffer' => $buffer,
                        ],
                    ]);
                } else {
                    $this->logger->debug('CMD OUTPUT', [
                        'buffer' => $buffer,
                    ]);
                }
            });

            $pid = $process->getPid();
            $process->wait();

            return 0 === $process->getExitCode();
        } catch (ProcessFailedException $exception) {
            $this->logger->error($exception->getMessage(), [
                'cmd' => $process->getCommandLine(),
                'file' => $javFile->getPath(),
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
        } catch (ProcessTimedOutException $exception) {
            $this->logger->error($exception->getMessage(), [
                'cmd' => $process->getCommandLine(),
                'file' => $javFile->getPath(),
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
                if (!posix_kill($pid, SIGTERM) || !posix_kill($pid, SIGKILL)) {
                    throw $exception;
                }
            }
        }

        return false;
    }

    /**
     * Method for addressing issue #38.
     *
     * @param JavFile $javFile
     */
    private function renameFromFilenameToInode(JavFile $javFile)
    {
        $filesystem = new Filesystem();
        $pathInfo = pathinfo($javFile->getPath());

        $oldPath = "{$pathInfo['dirname']}/{$pathInfo['filename']}.jpg";

        if ($filesystem->exists($oldPath)) {
            if (!$filesystem->exists($this->getThumbPath($javFile))) {
                $this->logger->debug('Renaming thumbnail', [
                    'oldpath' => $oldPath,
                    'newpath' => $this->getThumbPath($javFile),
                ]);
                $filesystem->rename($oldPath, $this->getThumbPath($javFile));
            } else {
                $this->logger->debug('Duplicate thumbnail detected during rename', [
                    'oldpath' => $oldPath,
                    'newPath' => $this->getThumbPath($javFile),
                ]);
                $filesystem->remove($oldPath);
            }
        }
    }

    public function getThumbnail(JavFile $javFile)
    {
        $this->renameFromFilenameToInode($javFile);

        if (file_exists($this->getThumbPath($javFile))) {
            return new \SplFileInfo($this->getThumbPath($javFile));
        }

        return false;
    }

    private function getThumbPath(JavFile $javFile)
    {
        return "{$this->javToolboxMediaThumbDirectory}/{$javFile->getInode()->getId()}.jpg";
    }
}
