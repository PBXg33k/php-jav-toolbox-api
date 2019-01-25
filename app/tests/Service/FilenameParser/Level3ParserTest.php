<?php
namespace App\Tests\Service\FilenameParser;


use App\Service\FilenameParser\Level3Parser;

class Level3ParserTest extends FilenameParserTest
{
    /**
     * Looks like level3parser is obsolete
     * @todo confirm
     */
    protected function setUp()
    {
        $this->setParser(new Level3Parser());
        $this->setMatchExpects([
            'hjd2048.com-ABC-012'       => false,
            '000_ABC-123_'              => true,
            '000234ABC-123_'            => true,
            'ABC-123_'                  => false,
            'dasf ABC-123 asoldinasdoi' => false,
            '138541384ABC-123'          => true,
            '00098_ABC-123'             => true,
        ]);
    }
}
