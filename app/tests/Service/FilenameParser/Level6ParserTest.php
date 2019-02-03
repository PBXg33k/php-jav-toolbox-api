<?php
namespace App\Tests\Service\FilenameParser;

use App\Service\FilenameParser\Level6Parser;

class Level6ParserTest extends FilenameParserTest
{
    protected function setUp()
    {
        $this->setParser(new Level6Parser());
        $this->setMatchExpects([
            '/path/ABC-012.mp4'                   => true,
            '/path/ABC012.mp4'                    => false,
            '/path/ABC012-1.mp4'                  => false,
            '/path/ABC-012-1.mp4'                 => false,
            '/path/ABC.024.mp4'                   => false,
            '/path/000234ABC-123_'                => false,
            '/path/000_ABC-123_.mp4'              => false,
            '/path/138541384ABC-123.mp4'          => false,
            '/path/00098_ABC-123.mp4'             => false,
            '/path/0901ABC123.mp4'                => false,
            '/path/ABC-123_.mp4'                  => true,
            '/path/ABC-123 Some Title.mp4'        => true,
            '/path/hjd2048.com-ABC-012.mp4'       => true,
            '/path/ABC-012hd.mp4'                 => true,
            '/path/ABC-012cd1.mp4'                => true,
            '/path/ABC-012V.mp4'                  => true,
            '/path/ABC-012-cd1.mp4'               => false,
            '/path/ABC-012-01.mp4'                => false,
            '/path/ABC-012-V.mp4'                 => false,
            '/path/ABC-012HDA.mp4'                => true,
            '/path/ABC-012-1.avi.mp4'             => false,
            '/path/dasf ABC-123 asoldinasdoi.mp4' => false,
            '/path/ABC-123 asoldinasdoi.mp4'      => true,
        ]);
    }
}
