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
