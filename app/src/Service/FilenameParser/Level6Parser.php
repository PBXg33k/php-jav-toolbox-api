<?php

namespace App\Service\FilenameParser;

class Level6Parser extends BaseParser
{
    /**
     * Level6Parser constructor.
     *
     * matches:
     * ABC012 Space delimited title
     * ABC-012 Space delimited title
     *
     * WARNING: This pattern may match false positives on multipart releases.
     * Make sure to run a more strict pattern before using this
     *
     * The following examples have false positives
     * ABC-012cd1
     * ABC-012V (roman numeral)
     * ABC-012HDA
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            self::PREG_LABEL,
            "\-",
            self::PREG_RELEASE,
            "[\w\s!]+?"
        );
    }
}
