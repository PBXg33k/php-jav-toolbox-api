<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

class EntityUpdatedEvent extends Event
{
    const NAME = 'entity.updated';

    /**
     * @var object
     */
    private $entity;

    public function __construct(object $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }
}
