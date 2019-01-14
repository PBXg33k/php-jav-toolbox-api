<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

/**
 * Class JAVTitle
 * @package App\Model
 */
class JAVTitle
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $release;

    /**
     * @var ArrayCollection|JAVFile
     */
    protected $files;

    /**
     * @var ?int
     */
    protected $part;

    /**
     * @var bool
     */
    protected $multipart = false;

    /**
     * @var string
     */
    protected $cleanName;

    /**
     * @var string
     */
    protected $parser;

    public function __construct()
    {
        $this->files = new ArrayCollection();
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
        $this->label = strtoupper($label);
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

    public function addFile(JAVFile $file): JAVTitle
    {
        $this->files->add($file);

        return $this;
    }

    public function setFile($key, JAVFile $file): JAVTitle
    {
        if($this->files->get($key))
            throw new DuplicateKeyException("{$key} already exists");

        $this->files->set($key, $file);

        return $this;
    }

    public function removeFile(JAVFile $file) : JAVTitle
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function getFiles(): ArrayCollection
    {
        return $this->files;
    }

    /**
     * @return bool
     */
    public function isMultipart(): bool
    {
        return $this->multipart;
    }

    /**
     * @param bool $multipart
     * @return JAVTitle
     */
    public function setMultipart(bool $multipart): JAVTitle
    {
        $this->multipart = $multipart;
        return $this;
    }

    /**
     * @return int
     */
    public function getPart(): ?int
    {
        return $this->part;
    }

    /**
     * @param int|string $part
     * @return JAVTitle
     * @throws \Exception If $part is neither an integer or a string
     */
    public function setPart($part): JAVTitle
    {
        if(is_string($part)) {
            // convert to integer
            $part = ord(strtolower($part)) - 96;
        } elseif (!is_int($part)) {
            throw new \Exception('part must be either an integer or a string');
        }

        $this->part = $part;
        return $this;
    }

    /**
     * @return string
     */
    public function getParser(): string
    {
        return $this->parser;
    }

    /**
     * @param string $parser
     * @return JAVTitle
     */
    public function setParser(string $parser): JAVTitle
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @return string
     */
    public function getCleanName(): string
    {
        return $this->cleanName;
    }

    /**
     * @param string $cleanName
     * @return JAVTitle
     */
    public function setCleanName(string $cleanName): JAVTitle
    {
        $this->cleanName = $cleanName;
        return $this;
    }
}
