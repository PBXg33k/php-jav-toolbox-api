<?php
/**
 * Created by PhpStorm.
 * User: PBX_g33k
 * Date: 30/03/2018
 * Time: 14:23
 */

namespace App\Model;
use Symfony\Component\Finder\SplFileInfo;


/**
 * Class JAVTitle
 * @package App\Model
 */
class JAVTitle
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $release;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var string
     */
    protected $xxhash;

    /**
     * @var string
     */
    protected $md5;

    /**
     * @var string
     */
    protected $sha1;

    /**
     * @var SplFileInfo
     */
    protected $file;

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return JAVTitle
     */
    public function setFilename(string $filename): JAVTitle
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return JAVTitle
     */
    public function setLabel(string $label): JAVTitle
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return int
     */
    public function getRelease(): int
    {
        return $this->release;
    }

    /**
     * @param int $release
     * @return JAVTitle
     */
    public function setRelease(int $release): JAVTitle
    {
        $this->release = $release;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return JAVTitle
     */
    public function setLength(int $length): JAVTitle
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return JAVTitle
     */
    public function setSize(int $size): JAVTitle
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return JAVTitle
     */
    public function setHeight(int $height): JAVTitle
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return JAVTitle
     */
    public function setWidth(int $width): JAVTitle
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return string
     */
    public function getXxhash(): string
    {
        return $this->xxhash;
    }

    /**
     * @param string $xxhash
     * @return JAVTitle
     */
    public function setXxhash(string $xxhash): JAVTitle
    {
        $this->xxhash = $xxhash;
        return $this;
    }

    /**
     * @return string
     */
    public function getMd5(): string
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     * @return JAVTitle
     */
    public function setMd5(string $md5): JAVTitle
    {
        $this->md5 = $md5;
        return $this;
    }

    /**
     * @return string
     */
    public function getSha1(): string
    {
        return $this->sha1;
    }

    /**
     * @param string $sha1
     * @return JAVTitle
     */
    public function setSha1(string $sha1): JAVTitle
    {
        $this->sha1 = $sha1;
        return $this;
    }

    /**
     * @return SplFileInfo
     */
    public function getFile(): SplFileInfo
    {
        return $this->file;
    }

    /**
     * @param SplFileInfo $file
     * @return JAVTitle
     */
    public function setFile(SplFileInfo $file): JAVTitle
    {
        $this->file = $file;
        return $this;
    }


}