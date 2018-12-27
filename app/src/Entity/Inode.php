<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileHashRepository")
 */
class Inode
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $md5;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $sha1;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $sha512;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $xxhash;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JavFile", mappedBy="inodeinfo")
     */
    private $javFiles;

    public function __construct()
    {
        $this->javFiles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    /**
     * @return Collection|JavFile[]
     */
    public function getJavFiles(): Collection
    {
        return $this->javFiles;
    }

    public function addJavFile(JavFile $javFile): self
    {
        if (!$this->javFiles->contains($javFile)) {
            $this->javFiles[] = $javFile;
            $javFile->setInodeinfo($this);
        }

        return $this;
    }

    public function removeJavFile(JavFile $javFile): self
    {
        if ($this->javFiles->contains($javFile)) {
            $this->javFiles->removeElement($javFile);
            // set the owning side to null (unless already changed)
            if ($javFile->getInodeinfo() === $this) {
                $javFile->setInodeinfo(null);
            }
        }

        return $this;
    }
}
