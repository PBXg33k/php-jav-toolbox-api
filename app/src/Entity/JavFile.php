<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JavFileRepository")
 */
class JavFile extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Title", inversedBy="files", cascade={"persist"})
     */
    private $title;

    /**
     * @var Inode
     * @ORM\ManyToOne(targetEntity="App\Entity\Inode", inversedBy="javFiles", cascade={"persist"})
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        if (!$this->getFilename()) {
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
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'part' => $this->part,
            'filename' => $this->filename,
            'path' => $this->path,
            'title' => $this->title,
            'inode' => $this->inode->jsonSerialize()
        ];
    }
}
