<?php
namespace App\Tests\Service\FilenameParser;

use App\Service\FilenameParser\Level2Parser;

class Level2ParserTest extends FilenameParserTest
{
    protected function setUp()
    {
        $this->setParser(new Level2Parser());
        $this->setMatchExpects([
            '/path/ABC-012.mp4'                   => true,
            '/path/ABC012.mp4'                    => false,
            '/path/ABC012-1.mp4'                  => false,
            '/path/ABC-012-1.mp4'                 => true,
            '/path/ABC.024.mp4'                   => false,
            '/path/000_ABC-123_.mp4'              => false,
            '/path/ABC-123_.mp4'                  => true,
            '/path/ABC-123 Some Title.mp4'        => false,
            '/path/hjd2048.com-ABC-012.mp4'       => true,
            '/path/ABC-012hd.mp4'                 => true,
            '/path/ABC-012cd1.mp4'                => true,
            '/path/ABC-012V.mp4'                  => true,
            '/path/ABC-012-cd1.mp4'               => true,
            '/path/ABC-012-01.mp4'                => true,
            '/path/ABC-012-V.mp4'                 => true,
            '/path/ABC-012HDA.mp4'                => true,
            '/path/ABC-012-1.avi.mp4'             => true,
            '/path/dasf ABC-123 asoldinasdoi.mp4' => false,
        ]);
    }
}
