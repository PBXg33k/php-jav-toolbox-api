<?php

namespace App\Service\FilenameParser;

class Level5Parser extends BaseParser
{
    /**
     * Level1Parser constructor.
     *
     * matches:
     * ABC012
     * ABC012cd1
     * ABC012-1
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            '\-',
            self::PREG_RELEASE,
            '\-?',
            self::PREG_SIMPLE_PART
        );
    }
}
