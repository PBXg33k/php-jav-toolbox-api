<?php
namespace App\Service\FilenameParser;


use App\Model\JAVTitle;

abstract class BaseParser
{
    const PREG_LABEL   = '(?<label>3dsvr|1[1-9]id|9snis|9soe|2[0-9]id|[a-z]{2,7})';
    const PREG_RELEASE = '(?<release>[0-9]{2,7})';
    const PREG_PART    = '(?:(?:\W|\_|\-|hhb|hd|cd|sc|disk\s?)(?<part>[1-9]|(?:[abcd]|[f][^hd])|[01][1-9]?|[IVX]+))?';
    const PREG_SIMPLE_PART  = '(?<part>0?[1-9]+|[abcde])?';

    private $blacklistRegex = [
        "[\d]{2}[\s\-\_\.]([0-1]?[0-9]|jan|feb|mar|apr|jun|jul|aug|sep|okt|nov|dec)[\s\-\_\.][1-2]?[0-9]{2,3}"
    ];

    private $blacklistLabel = [
        'fullhd',
        'vol'
    ];

    private $filterWords = [
        '1080p',
        '720p',
        '540p',
        '480p',
        '4k',
        'e-body',
        'fullhd',
        'full-hd',
        'fhd',
        '[hd]',
        '[44x.me]',
        '[thz.la]',
        'webrip',
        'dvdrip',
        'hdrip',
        'hdtvrip',
        'bdrip',
        'h264',
        'hq',
        '60fps',
        '30fps',
        '.avi',
        '.mkv',
        '.mp4',
        '.wmv',
        '(non-nude)',
        'non-nude',
        '[   ]',
        '~',
        '()',
        '_',
        '.',
    ];

    private $leftTrim = [
        'hjd2048.com-',
        'hjd2048.com',
        'watch18plus_',
    ];

    private $romanNumerals = array(
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    );

    private $rightTrim = [
        'hd',
        'sd',
        'mp4',
        ' -',
        '-f',
        '-5',
        '.',
        'avi',
    ];

    /**
     * @var array
     */
    protected $matches;

    private $filename;

    public $pattern;

    public abstract function __construct();

    public function hasMatch(string $path): bool
    {
        if(!$this->pattern) {
            throw new \Exception('pattern not set');
        }

        $path       = $this->cleanUp($path);
        $this->filename = $path;

        $match = preg_match($this->pattern, $path, $this->matches);
        if($match) {
            if(
                in_array(strtolower($this->matches['label']),$this->blacklistLabel)
            ) {
                return false;
            }
        }

        return $match;
    }

    public function cleanUp(string &$filename): string
    {
        $filename = trim(self::rtrim(
            self::ltrim(
                str_ireplace(
                    $this->filterWords,
                    ' ',
                    $this->extractFilename($filename)
                ),
                $this->leftTrim
            ),
            $this->rightTrim
        ));

        foreach ($this->blacklistRegex as $blacklistRegex) {
            if(preg_match(sprintf("~(%s)~i", $blacklistRegex),$filename, $matches)) {
                $filename = str_replace($matches[0], '', $filename);
            }
        }

        return $filename;
    }

    public function getParts(): JAVTitle
    {
        $title = (new JAVTitle())
            ->setLabel($this->matches['label'])
            ->setRelease($this->matches['release'])
            ->setParser(static::class)
            ->setCleanName($this->cleanUp($this->filename));

        if(isset($this->matches['part']) && !is_null($this->matches['part'])) {
            $part = $this->matches['part'];
            if(!is_numeric($part)) {
                // Check if part is a roman numeral
                if(preg_match('/^[I V X]*$/', $part)) {
                    $val = 0;
                    foreach($this->romanNumerals as $key => $value) {
                        while (strpos($part, $key) === 0) {
                            $val += $value;
                            $part = substr($part, strlen($key));
                        }
                    }

                    $part = $val;
                } else {
                    $part = ord(strtolower($part)) - 96;
                }
            } else {
                $part = (int)$part;
            }
            if(abs($part) !== $part) {
                throw new \Exception(
                    'Parsing error (got '. $part .')on: '. json_encode($this->matches) .'\nUsing REGEX: '.$this->pattern
                );
            }

            $title->setPart($part);
        }

        return $title;
    }

    protected function constructRegexPattern(string ...$parts) {
        $this->pattern = sprintf('~^%s$~i', implode("", $parts));
        return $this->pattern;
    }

    private function extractFilename(string $path) {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    private static function ltrim(string $filename, array $leftTrim): string
    {
        foreach ($leftTrim as $trim) {
            if(stripos(strtolower($filename), $trim) === 0) {
                $filename = substr($filename, strlen($trim));
                $filename = self::ltrim($filename, $leftTrim);
            }
        }

        return $filename;
    }

    private static function rtrim(string $filename, array $rightTrim): string
    {
        if (preg_match('~(?<dupe>\([\d+]\))$~', $filename, $matches)) {
            $filename = rtrim($filename, $matches['dupe'].' ');
        }

            // Parse filename to exclude exces filtering if filtered word is part of release
        foreach ($rightTrim as $trim) {
            if(stripos($filename, $trim) === strlen($filename) - strlen($trim)) {
                $filename = substr($filename, 0, -1 * abs(strlen($trim)));
                $filename = rtrim($filename, '-');
                $filename = self::rtrim($filename, $rightTrim);
            }
        }

        return $filename;
    }
}
