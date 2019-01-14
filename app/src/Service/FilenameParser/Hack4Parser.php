<?php
namespace App\Service\FilenameParser;

class Hack4Parser extends BaseParser
{
    /**
     * Level1Parser constructor.
     *
     * matches:
     * KV-145-4K-1
     * kv-136-fhd-01
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            '\-',
            self::PREG_RELEASE,
            "[\-\s]+",
            self::PREG_SIMPLE_PART
        );
    }
}
