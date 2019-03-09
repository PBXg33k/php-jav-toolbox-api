<?php

namespace App\Service\FilenameParser;

class CustomParserHjd2048 extends BaseParser
{
    /**
     * Level4Parser constructor.
     *
     * Matches:
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "(?:hjd2048\-[\d]+)",
            self::PREG_LABEL,
            self::PREG_RELEASE,
            self::PREG_SIMPLE_PART
        );
    }
}
