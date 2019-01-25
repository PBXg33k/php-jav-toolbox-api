<?php
namespace App\Service\FilenameParser;

class Level4Parser extends BaseParser
{
    /**
     * Level4Parser constructor.
     *
     * matches:
     * 0901ABC123
     *
     * @obsolete already covered by Level3Parser
     */

    public function __construct()
    {
        $this->constructRegexPattern(
            "\d+",
            self::PREG_LABEL,
            self::PREG_RELEASE,
            self::PREG_PART
        );
    }
}
