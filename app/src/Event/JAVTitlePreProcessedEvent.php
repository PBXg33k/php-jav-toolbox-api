<?php

namespace App\Event;

use App\Entity\JavFile;
use App\Entity\Title;
use Symfony\Component\EventDispatcher\Event;

class JAVTitlePreProcessedEvent extends Event
{
    const NAME = 'jav.title.preprocessed';

    /**
     * @var Title
     */
    protected $title;

    /**
     * @var JavFile
     */
    protected $file;

    public function __construct(Title $title, JavFile $javFile)
    {
        $this->title = $title;
        $this->file = $javFile;
    }

    /**
     * @return Title
     */
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * @return JavFile
     */
    public function getFile(): JavFile
    {
        return $this->file;
    }
}
