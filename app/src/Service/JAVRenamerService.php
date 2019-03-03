<?php

namespace App\Service;

use App\Entity\JAVFile;
use App\Entity\Title;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class JAVRenamerService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->filesystem = new Filesystem();
    }

    public function renameFile(Title $title, bool $dryRun = false)
    {
        /** @var JAVFile $file */
        foreach ($title->getFiles() as $file) {
            if (!$this->filesystem->exists($file->getPath())) {
                $this->logger->error('File not found', [
                    'path' => $file->getPath(),
                ]);
                throw new FileNotFoundException();
            }

            $targetFilename = sprintf('[%s]', $title->getCatalognumber());
            if ($file->getPart()) {
                $targetFilename .= '-'.$file->getPart();
            }

            $targetFilename .= '.'.pathinfo($file->getPath(), PATHINFO_EXTENSION);

            $this->logger->info('Renaming file', [
                'old_filename' => $file->getFilename(),
                'new_filename' => $targetFilename,
            ]);

            if (!$dryRun) {
                $this->filesystem->rename($file->getPath(), pathinfo($file->getPath(), PATHINFO_DIRNAME).'/'.$targetFilename);
            }
        }
    }

    /**
     * @param Filesystem $filesystem
     *
     * @return JAVRenamerService
     */
    public function setFilesystem(Filesystem $filesystem): JAVRenamerService
    {
        $this->filesystem = $filesystem;

        return $this;
    }
}
