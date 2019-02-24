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
    )
    {
        $this->file             = $file;
        $this->relativePath     = $relativePath;
        $this->relativePathname = $relativePathname;
    }

    /**
     * @return SplFileInfo
     */
    public function getFileInfo(): SplFileInfo
    {
        if(!$this->fileInfo) {
            $this->fileInfo = new SplFileInfo($this->file, $this->relativePath, $this->relativePathname);
        }

        return $this->fileInfo;
    }
}
