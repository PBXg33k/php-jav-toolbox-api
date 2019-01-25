<?php
namespace App\Tests\Service\FilenameParser;

use App\Service\FilenameParser\Level2Parser;

class Level2ParserTest extends FilenameParserTest
{
    protected function setUp()
    {
        $this->setParser(new Level2Parser());
        $this->setMatchExpects([
            'hjd2048.com-ABC-012'       => true,
            'ABC-012hd'                 => true,
            'ABC-012'                   => true,
            'ABC-012-cd1'               => true,
            'ABC-012-01'                => true,
            'ABC-012-1'                 => true,
            'ABC-012-V'                 => true,
            'ABC-012HDA'                => true,
            'ABC-012-1.avi'             => true,
            'dasf ABC-123 asoldinasdoi' => false,
        ]);
    }
}
