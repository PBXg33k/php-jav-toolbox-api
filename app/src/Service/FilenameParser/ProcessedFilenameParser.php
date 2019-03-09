<?php

namespace App\Service\FilenameParser;

class ProcessedFilenameParser extends BaseParser
{
    /**
     * ProcessedFilenameParser constructor.
     *
     * Matches:
     * [ABC-012]
     * [ABC-012]-A
     * [ABC-012]-1
     * [ABC-012]-CD1
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "\[",
            self::PREG_LABEL,
            "\-",
            self::PREG_RELEASE,
            "\]",
            self::PREG_PART
        );
    }
}
