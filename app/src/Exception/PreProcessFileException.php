<?php
namespace App\Exception;


use Throwable;

class PreProcessFileException extends \Exception
{
    protected $preProcessFile;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, $preprocessFile)
    {
        $this->preProcessFile = $preprocessFile;
        parent::__construct($message, $code, $previous);
    }

    public function getPreProcessFile()
    {
        return $this->preProcessFile;
    }
}