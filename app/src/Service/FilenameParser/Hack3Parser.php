<?php

namespace App\Service\FilenameParser;

class Hack3Parser extends BaseParser
{
    /**
     * Level1Parser constructor.
     *
     * matches:
     * hey-4160-008_
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            '\-',
            self::PREG_RELEASE,
            "\-008\_"
        );
    }
}
