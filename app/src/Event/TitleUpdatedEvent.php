<?php
namespace App\Event;

use App\Entity\Title;
use Symfony\Component\EventDispatcher\Event;

class TitleUpdatedEvent extends Event
{
    const NAME = 'title.updated';

    /**
     * @var Title
     */
    private $title;

    public function __construct(Title $title)
    {
        $this->title = $title;
    }

    /**
     * @return Title
     */
    public function getTitle(): Title
    {
        return $this->title;
    }
}
