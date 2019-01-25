<?php
namespace App\Tests\Service\FilenameParser;


use App\Service\FilenameParser\Level1Parser;

class Level1ParserTest extends FilenameParserTest
{
    protected function setUp()
    {
        $this->setParser(new Level1Parser());
        $this->setMatchExpects([
            'ABC-012'                   => true,
            'ABC012'                    => true,
            'ABC012-1'                  => true,
            'ABC-012-1'                 => true,
            'ABC.024'                   => false,
            '000_ABC-123_'              => false,
            'ABC-123_'                  => true,
            'ABC-123 Some Title'        => false,
            'hjd2048.com-ABC-012'       => true,
            'ABC-012hd'                 => true,
            'ABC-012cd1'                => true,
            'ABC-012V'                  => false,
            'ABC-012-cd1'               => false,
            'ABC-012-01'                => true,
            'ABC-012-V'                 => true,
            'ABC-012HDA'                => true,
            'ABC-012-1.avi'             => true,
            'dasf ABC-123 asoldinasdoi' => false,
        ]);
    }
}
