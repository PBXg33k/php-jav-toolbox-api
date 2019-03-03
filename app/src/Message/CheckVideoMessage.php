<?php

namespace App\Message;

class CheckVideoMessage extends JavFileMessage
{
    private $callback;

    public function __construct(int $javFileId, callable $callback = null)
    {
        $this->callback = $callback;
        parent::__construct($javFileId);
    }

    /**
     * @return callable
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }
}
