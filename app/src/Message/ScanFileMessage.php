<?php

namespace App\Message;

use Symfony\Component\Finder\SplFileInfo;

class ScanFileMessage
{
    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    private $file;

    private $relativePath;

    private $relativePathname;

    public function __construct(
        string $file,
        string $relativePath,
        string $relativePathname
    ) {
        $this->file = $file;
        $this->relativePath = $relativePath;
        $this->relativePathname = $relativePathname;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * @return string
     */
    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }

    /**
     * @return SplFileInfo
     */
    public function constructFileInfo(): SplFileInfo
    {
        if (!$this->fileInfo) {
            $this->fileInfo = new SplFileInfo($this->file, $this->relativePath, $this->relativePathname);
        }

        return $this->fileInfo;
    }
}
