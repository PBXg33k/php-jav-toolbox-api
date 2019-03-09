<?php

namespace App\Service\FilenameParser;

class Level40Parser extends BaseParser
{
    public function __construct()
    {
        $this->constructRegexPattern(
            ".*?\W",
            self::PREG_LABEL,
            "\W?",
            self::PREG_RELEASE,
            "\W?",
            self::PREG_PART,
            '(?:.*)?'
        );
    }
}
