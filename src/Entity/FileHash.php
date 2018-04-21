<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileHashRepository")
 */
class FileHash
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $md5;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $sha1;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $sha512;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $xxhash;

    public function getId()
    {
        return $this->id;
    }

    public function getMd5(): ?string
    {
        return $this->md5;
    }

    public function setMd5(string $md5): self
    {
        $this->md5 = $md5;

        return $this;
    }

    public function getSha1(): ?string
    {
        return $this->sha1;
    }

    public function setSha1(string $sha1): self
    {
        $this->sha1 = $sha1;

        return $this;
    }

    public function getSha512(): ?string
    {
        return $this->sha512;
    }

    public function setSha512(string $sha512): self
    {
        $this->sha512 = $sha512;

        return $this;
    }

    public function getXxhash(): ?string
    {
        return $this->xxhash;
    }

    public function setXxhash(string $xxhash): self
    {
        $this->xxhash = $xxhash;

        return $this;
    }
}
