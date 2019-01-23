<?php
namespace App\Service\FilenameParser;

use App\Service\FilenameParser;
use PHPUnit\Framework\TestCase;

class FilenameParserTest extends TestCase
{
    /**
     * @test
     */
    public function baseTest()
    {
        $parser = new FilenameParser\Level1Parser();

        $filterSamples = [
            'KV-142(60fps).mp4' => 'KV-142( )',
            'HUNTA-228 [WebRip_720p]_[only scene with Mishima Natsuko].mp4' => 'HUNTA-228   [only scene with Mishima Natsuko]'
        ]
    }
}
