<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileHashRepository")
 */
class Inode extends BaseEntity
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
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $checked = false;

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
     * @var int
     * @ORM\Column(type="bigint", unique=false, options={"unsigned"=true})
     */
    private $filesize;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $processed = false;

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
     * @return bool
     */
    public function isChecked(): ?bool
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     *
     * @return Inode
     */
    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return Inode
     */
    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return Inode
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return float
     */
    public function getFps(): ?float
    {
        return $this->fps;
    }

    /**
     * @param float $fps
     *
     * @return Inode
     */
    public function setFps(float $fps): self
    {
        $this->fps = $fps;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodec(): ?string
    {
        return $this->codec;
    }

    /**
     * @param string $codec
     *
     * @return Inode
     */
    public function setCodec(string $codec): self
    {
        $this->codec = $codec;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConsistent(): ?bool
    {
        return $this->consistent;
    }

    /**
     * @param bool $consistent
     *
     * @return Inode
     */
    public function setConsistent(bool $consistent): self
    {
        $this->consistent = $consistent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     *
     * @return Inode
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @param int $length
     *
     * @return Inode
     */
    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getBitrate(): ?int
    {
        return $this->bitrate;
    }

    /**
     * @param int $bitrate
     *
     * @return Inode
     */
    public function setBitrate(int $bitrate): self
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    /**
     * @return int
     */
    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    /**
     * @param int $filesize
     *
     * @return Inode
     */
    public function setFilesize(int $filesize): self
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * @return bool
     */
    public function isProcessed(): ?bool
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     *
     * @return Inode
     */
    public function setProcessed(bool $processed): self
    {
        $this->processed = $processed;

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

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id'        => $this->id,
            'md5'       => $this->md5,
            'sha1'      => $this->sha1,
            'sha512'    => $this->sha512,
            'xxhash'    => $this->xxhash,
            'checked'   => $this->checked === true,
            'height'    => $this->height,
            'width'     => $this->width,
            'fps'       => $this->fps,
            'codec'     => $this->codec,
            'consistent'=> $this->consistent,
            'meta'      => $this->meta,
            'length'    => $this->length,
            'bitrate'   => $this->bitrate,
            'filesize'  => $this->filesize,
            'processed' => $this->processed
        ];
    }
}
