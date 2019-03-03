<?php

namespace App\Service\FilenameParser;

class CustomSkyParser extends BaseParser
{
    public function __construct()
    {
        $this->constructRegexPattern(
            "Sky Angel vol \d+ \(([\w\s]+)\)\[",
            self::PREG_LABEL,
            "\-",
            self::PREG_RELEASE,
            "\]([\(\d\.\)]+)?"
        );
    }
}
