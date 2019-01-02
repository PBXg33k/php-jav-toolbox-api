<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JavFileRepository")
 */
class JavFile
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $part;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $filename;

    /**
     * @var int
     * @ORM\Column(type="bigint", unique=false, options={"unsigned"=true})
     */
    private $filesize;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $processed;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $path;

    /**
     * @var ?Title
     * @ORM\ManyToOne(targetEntity="App\Entity\Title", inversedBy="files", cascade={"persist"})
     */
    private $title;

    /**
     * @var Inode
     * @ORM\ManyToOne(targetEntity="App\Entity\Inode", inversedBy="javFiles", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $inode;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    private $fps;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codec;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $consistent;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $meta;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $length;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bitrate;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $checked;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

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
        if(!$this->getFilename()) {
            $this->setFilename(pathinfo($path, PATHINFO_BASENAME));
        }
        $this->path = $path;

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

    public function getInode(): ?Inode
    {
        return $this->inode;
    }

    public function setInode(Inode $inode): self
    {
        $this->inode = $inode;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getFps(): ?float
    {
        return $this->fps;
    }

    public function setFps(?float $fps): self
    {
        $this->fps = $fps;

        return $this;
    }

    public function getCodec(): ?string
    {
        return $this->codec;
    }

    public function setCodec(?string $codec): self
    {
        $this->codec = $codec;

        return $this;
    }

    public function getConsistent(): ?bool
    {
        return $this->consistent;
    }

    public function setConsistent(?bool $consistent): self
    {
        $this->consistent = $consistent;

        return $this;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function setMeta($meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getBitrate(): ?int
    {
        return $this->bitrate;
    }

    public function setBitrate(?int $bitrate): self
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(?bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }
}
