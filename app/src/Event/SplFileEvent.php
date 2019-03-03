<?php

namespace App\Event;

use SplFileInfo;
use Symfony\Component\EventDispatcher\Event;

abstract class SplFileEvent extends Event
{
    /**
     * @var SplFileInfo
     */
    protected $file;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }

    /**
     * @return SplFileInfo
     */
    public function getFile(): SplFileInfo
    {
        return $this->file;
    }
}
