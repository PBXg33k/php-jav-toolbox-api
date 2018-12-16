<?php
namespace App\Model;
use Symfony\Component\Finder\SplFileInfo;

class JAVFile
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var int
     */
    protected $part;

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
     * @return JAVFile
     */
    public function setFilename(string $filename): JAVFile
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return int
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * @param int $part
     * @return JAVFile
     */
    public function setPart(int $part): JAVFile
    {
        $this->part = $part;
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
     * @return JAVFile
     */
    public function setLength(int $length): JAVFile
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
     * @return JAVFile
     */
    public function setSize(int $size): JAVFile
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
     * @return JAVFile
     */
    public function setHeight(int $height): JAVFile
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
     * @return JAVFile
     */
    public function setWidth(int $width): JAVFile
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
     * @return JAVFile
     */
    public function setXxhash(string $xxhash): JAVFile
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
     * @return JAVFile
     */
    public function setMd5(string $md5): JAVFile
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
     * @return JAVFile
     */
    public function setSha1(string $sha1): JAVFile
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
     * @return JAVFile
     */
    public function setFile(SplFileInfo $file): JAVFile
    {
        $this->file = $file;
        return $this;
    }


}