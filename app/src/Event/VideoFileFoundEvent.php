<?php
namespace App\Event;

/**
 * Class VideoFileFoundEvent
 * @package App\Event
 */
class VideoFileFoundEvent extends SplFileEvent
{
    /**
     * Event name
     */
    const NAME = 'videofile.found';
}