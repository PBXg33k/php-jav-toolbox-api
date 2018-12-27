<?php
namespace App\Event;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class VideoFileFoundEvent
 * @package App\Event
 */
class VideoFileFoundEvent extends Event
{
    /**
     * Event name
     */
    const NAME = 'videofile.found';

    /**
     * @var SplFileInfo
     */
    protected $file;

    /**
     * VideoFileFoundEvent constructor.
     * @param SplFileInfo $file
     */
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