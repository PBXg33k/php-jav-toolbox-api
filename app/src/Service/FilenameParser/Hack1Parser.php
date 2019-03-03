<?php

namespace App\Service\FilenameParser;

class Hack1Parser extends BaseParser
{
    /**
     * Level1Parser constructor.
     *
     * matches:
     * SNIS-941 [WebRip_720p] ~ Nami Hoshino
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            '\-?',
            self::PREG_RELEASE,
            self::PREG_SIMPLE_PART,
            "\s(?:-\s)?(?:[\s\[\]0-9a-z\~\_,\.\-\#\'!&\(\)]+)(\#\d)?"
        );
    }
}
