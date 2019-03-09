<?php

namespace App\Service\FilenameParser;

class Level12Parser extends BaseParser
{
    /**
     * Level12Parser constructor.
     *
     * matches:
     *      [abc.com]ABC-012
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "\[[\w\.]+\]",
            self::PREG_LABEL,
            "\-",
            self::PREG_RELEASE,
            self::PREG_PART
        );
    }
}
