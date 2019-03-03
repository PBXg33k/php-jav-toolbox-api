<?php

namespace App\Service\FilenameParser;

class Level10Parser extends BaseParser
{
    public function __construct()
    {
        $this->constructRegexPattern(
            'aukg364_sc3',
            self::PREG_LABEL,
            "\-?",
            self::PREG_RELEASE,
            '(?:.*?)',
            self::PREG_PART,
            "(?:\.[a-z]{3})?"
        );
    }
}
