<?php
namespace App\Message;


use Symfony\Component\Finder\SplFileInfo;

class ScanFileMessage
{
    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return SplFileInfo
     */
    public function getFileInfo(): SplFileInfo
    {
        return $this->fileInfo;
    }
}
