<?php
namespace App\Tests\Service\FilenameParser;

use App\Service\FilenameParser;
use PHPUnit\Framework\TestCase;

abstract class FilenameParserTest extends TestCase
{
    /**
     * @var FilenameParser\BaseParser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $matchExpects;

    protected function setParser(FilenameParser\BaseParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Sets an array of expectations for the parser
     * Input should be an array with filenames as index with result as value.
     * IE:
     * [
     *     'ABC-012' => true,
     *     'ANA?AD7' => false
     * ]
     *
     * @param array $expects
     * @return mixed
     */
    protected function setMatchExpects(array $expects)
    {
        $this->matchExpects = $expects;
    }

    /**
     * @test
     */
    public function baseTest()
    {
        $filterSamples = $this->getFilterSamples();

        foreach($filterSamples as $input => $assert) {
            $this->assertSame($assert, $this->parser->cleanUp($input));
        }
    }

    /**
     * @test
     */
    public function matchTest()
    {
        if(!is_array($this->matchExpects) || empty($this->matchExpects)) {
            $this->markTestIncomplete('matchExpects not populated');
        }

        foreach($this->matchExpects as $input => $expect) {
            $result = $this->parser->hasMatch($input);
            $this->assertEquals($expect, $result);

        }
    }

    protected function getFilterSamples()
    {
        return [
            'KV-142(60fps).mp4' => 'KV-142( )',
            'HUNTA-228 [WebRip_720p]_[only scene with Mishima Natsuko].mp4' => 'HUNTA-228 [   ] [only scene with Mishima Natsuko]'
        ];
    }
}
