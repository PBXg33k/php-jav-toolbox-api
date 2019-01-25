<?php
namespace App\Service\FilenameParser;

class Level2Parser extends BaseParser
{
    /**
     * Level2Parser constructor.
     *
     * matches:
     * ABC-012
     * ABC-012-cd1
     * ABC-012-01
     * ABC-012-1
     * ABC-012HDA
     * ABC-012-1.avi
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            '\-?',
            self::PREG_RELEASE,
            '(?:\-*)?',
            self::PREG_PART,
            '(?:\.[a-z]{3})?'
        );
    }
}
