<?php

namespace App\Exception;

use Doctrine\Common\Collections\ArrayCollection;
use Throwable;

class JavIDExtractionException extends \Exception
{
    /**
     * @var \SplFileInfo
     */
    private $fileinfo;

    /**
     * @var ArrayCollection
     */
    private $matchers;

    public function __construct(string $message = '', \SplFileInfo $fileInfo, array $matchers, int $code = 0, Throwable $previous = null)
    {
        $this->fileinfo = $fileInfo;
        $this->matchers = new ArrayCollection($matchers);

        if ('' == $message) {
            $message = 'JAV ID Extraction failed';
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \SplFileInfo
     */
    public function getFileinfo(): \SplFileInfo
    {
        return $this->fileinfo;
    }

    /**
     * @return ArrayCollection
     */
    public function getMatchers(): ArrayCollection
    {
        return $this->matchers;
    }
}
