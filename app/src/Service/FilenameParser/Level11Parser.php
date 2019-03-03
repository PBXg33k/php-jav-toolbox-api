<?php

namespace App\Service\FilenameParser;

class Level11Parser extends BaseParser
{
    /**
     * Level5Parser constructor.
     *
     * matches:
     *      0702prtd016
     *      0402abp709hhb2
     */
    public function __construct()
    {
        $this->constructRegexPattern(
            "[\S]+?",
            self::PREG_LABEL,
            self::PREG_RELEASE,
            self::PREG_PART,
            '.*'
        );
    }
}
