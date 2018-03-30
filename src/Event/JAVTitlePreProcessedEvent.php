<?php
namespace App\Event;


use App\Model\JAVTitle;
use Symfony\Component\EventDispatcher\Event;

class JAVTitlePreProcessedEvent extends Event
{
    const NAME = 'jav.title.preprocessed';

    /**
     * @var JAVTitle
     */
    protected $title;

    public function __construct(JAVTitle $title)
    {
        $this->title = $title;
    }

    /**
     * @return JAVTitle
     */
    public function getTitle(): JAVTitle
    {
        return $this->title;
    }
}