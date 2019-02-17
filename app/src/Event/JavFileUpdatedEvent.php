<?php
namespace App\Event;

use App\Entity\JavFile;
use Symfony\Component\EventDispatcher\Event;

class JavFileUpdatedEvent extends Event
{
    const NAME = 'javfile.updated';

    /**
     * @var JavFile
     */
    private $javFile;

    public function __construct(JavFile $javFile)
    {
        $this->javFile = $javFile;
    }

    /**
     * @return JavFile
     */
    public function getJavFile(): JavFile
    {
        return $this->javFile;
    }
}
