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
     * @var string
     * @ORM\Column(type="text")
     */
    private $path;

    /**
     * @var ?Title
     * @ORM\ManyToOne(targetEntity="App\Entity\Title", inversedBy="files")
     */
    private $title;

    /**
     * @var Inode
     * @ORM\ManyToOne(targetEntity="App\Entity\Inode", inversedBy="javFiles", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $inode;

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

    /**
     * @deprecated
     */
    public function getFilesize(): ?int
    {
        return $this->getInode()->getFilesize();
    }

    /**
     * @deprecated
     */
    public function setFilesize(int $filesize): self
    {
        $this->getInode()->setFilesize($filesize);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getProcessed(): ?bool
    {
        return $this->getInode()->isProcessed();
    }

    /**
     * @deprecated
     */
    public function setProcessed(bool $processed): self
    {
        $this->getInode()->setProcessed($processed);

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

    /**
     * @deprecated
     */
    public function getHeight(): ?int
    {
        return $this->getInode()->getHeight();
    }

    /**
     * @deprecated
     */
    public function setHeight(int $height): self
    {
        $this->getInode()->setHeight($height);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getWidth(): ?int
    {
        return $this->getInode()->getWidth();
    }

    /**
     * @deprecated
     */
    public function setWidth(?int $width): self
    {
        $this->getInode()->setWidth($width);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getFps(): ?float
    {
        return $this->getInode()->getFps();
    }

    /**
     * @deprecated
     */
    public function setFps(?float $fps): self
    {
        $this->getInode()->setFps($fps);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getCodec(): ?string
    {
        return $this->getInode()->getCodec();
    }

    /**
     * @deprecated
     */
    public function setCodec(?string $codec): self
    {
        $this->getInode()->setCodec($codec);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getConsistent(): ?bool
    {
        return $this->getInode()->isConsistent();
    }

    /**
     * @deprecated
     */
    public function setConsistent(?bool $consistent): self
    {
        $this->getInode()->setCodec($consistent);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getMeta()
    {
        return $this->getInode()->getMeta();
    }

    /**
     * @deprecated
     */
    public function setMeta($meta): self
    {
        $this->getInode()->setMeta($meta);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getLength(): ?int
    {
        return $this->getInode()->getLength();
    }

    /**
     * @deprecated
     */
    public function setLength(?int $length): self
    {
        $this->getInode()->setLength($length);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getBitrate(): ?int
    {
        return $this->getInode()->getBitrate();
    }

    /**
     * @deprecated
     */
    public function setBitrate(?int $bitrate): self
    {
        $this->getInode()->setBitrate($bitrate);

        return $this;
    }

    /**
     * @deprecated
     */
    public function getChecked(): ?bool
    {
        return $this->getInode()->isChecked();
    }

    /**
     * @deprecated
     */
    public function setChecked(?bool $checked): self
    {
        $this->getInode()->setChecked($checked);

        return $this;
    }
}
