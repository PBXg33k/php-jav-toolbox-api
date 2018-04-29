<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JavFileRepository")
 */
class JavFile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $part;

    /**
     * @ORM\Column(type="text")
     */
    private $filename;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     */
    private $filesize;

    /**
     * @ORM\Column(type="boolean")
     */
    private $processed;

    /**
     * @ORM\Column(type="text")
     */
    private $path;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\FileHash", cascade={"persist", "remove"})
     */
    private $hash;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Title", inversedBy="files")
     */
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true, unique=false, options={"unsigned"=true})
     */
    private $inode;

    public function getId()
    {
        return $this->id;
    }

    public function getPart(): ?int
    {
        return $this->part;
    }

    public function setPart(int $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(int $filesize): self
    {
        $this->filesize = $filesize;

        return $this;
    }

    public function getProcessed(): ?bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): self
    {
        $this->processed = $processed;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getHash(): ?FileHash
    {
        return $this->hash;
    }

    public function setHash(?FileHash $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getTitle(): ?Title
    {
        return $this->title;
    }

    public function setTitle(?Title $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getInode(): ?int
    {
        return $this->inode;
    }

    public function setInode(int $inode): self
    {
        $this->inode = $inode;

        return $this;
    }
}
