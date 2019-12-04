<?php

namespace App\Event;

use App\Exception\FileException;
use SplFileInfo;
use Symfony\Contracts\EventDispatcher\Event;

abstract class SplFileEvent extends Event
{
    /**
     * @var SplFileInfo
     */
    protected $file;

    public function __construct(string $path)
    {
        if(!is_file($path)) {
            throw new FileException('not a file: '.$path);
        }

        $this->file = new SplFileInfo($path);
    }

    /**
     * @return SplFileInfo
     */
    public function getFile(): SplFileInfo
    {
        return $this->file;
    }
}
