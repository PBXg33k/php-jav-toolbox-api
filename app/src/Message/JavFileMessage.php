<?php
namespace App\Message;

abstract class JavFileMessage
{
    /**
     * @var int
     */
    private $javFileId;

    public function __construct(int $javFileId)
    {
        $this->javFileId = $javFileId;
    }

    /**
     * @return int
     */
    public function getJavFileId(): int
    {
        return $this->javFileId;
    }
}
