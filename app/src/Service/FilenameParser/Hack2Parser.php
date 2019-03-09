<?php

namespace App\Service\FilenameParser;

class Hack2Parser extends BaseParser
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
            '\-',
            self::PREG_RELEASE,
            "\_?(?:[\s\[\]0-9a-z\~\_,\.\-\#\'!&\(\)\–]+)"
        );
    }
}
