<?php

namespace App\Service\FilenameParser;

class Hack5Parser extends BaseParser
{
    /**
     * Level1Parser constructor.
     *
     * matches:
     * ABC-012d1
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            '\-',
            self::PREG_RELEASE,
            "[\-\s\w]",
            self::PREG_SIMPLE_PART
        );
    }
}
