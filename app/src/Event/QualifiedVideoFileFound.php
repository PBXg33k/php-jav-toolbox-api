<?php
namespace App\Event;

/**
 * Class VideoFileFoundEvent
 * @package App\Event
 */
class QualifiedVideoFileFound extends SplFileEvent
{
    /**
     * Event name
     */
    const NAME = 'qualifiedvideofile.found';
}
