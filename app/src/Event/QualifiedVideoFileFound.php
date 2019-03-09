<?php

namespace App\Event;

/**
 * Class VideoFileFoundEvent.
 */
class QualifiedVideoFileFound extends SplFileEvent
{
    /**
     * Event name.
     */
    const NAME = 'qualifiedvideofile.found';
}
