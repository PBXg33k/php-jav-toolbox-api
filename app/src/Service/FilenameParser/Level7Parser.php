<?php
namespace App\Service\FilenameParser;

class Level7Parser extends BaseParser
{
    /**
     * Level3Parser constructor.
     *
     * matches:
     * FHD_ABC-123
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "fhd\_",
            self::PREG_LABEL,
            "\-?",
            self::PREG_RELEASE,
            self::PREG_PART
        );
    }
}
