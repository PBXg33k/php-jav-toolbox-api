<?php

namespace App\Service\FilenameParser;

class Level3Parser extends BaseParser
{
    /**
     * Level3Parser constructor.
     *
     * matches:
     * 000_ABC-012_
     * ABC-012_
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "[\d_]+",
            self::PREG_LABEL,
            "\-*?",
            self::PREG_RELEASE,
            self::PREG_PART,
            '_?'
        );
    }
}
