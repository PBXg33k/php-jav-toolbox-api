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
     * @var bool
     */
    protected $multipart = false;

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


}
