<?php
namespace App\Service\FilenameParser;

class CustomMarozParser extends BaseParser
{
    /**
     * MarozParser constructor.
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "(?:MarOz[\_\-]+)",
            self::PREG_LABEL,
            "\-",
            self::PREG_RELEASE,
            "(?:[\-\_\w\.\d]+)?",
            self::PREG_PART
        );
    }
}
