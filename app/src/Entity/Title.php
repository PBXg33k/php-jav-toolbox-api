<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TitleRepository")
 */
class Title
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name_romaji;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name_japanese;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $catalognumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JavFile", mappedBy="title", cascade={"persist"})
     */
    private $files;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Model", inversedBy="titles", cascade={"persist"})
     */
    private $models;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->models = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNameRomaji(): ?string
    {
        return $this->name_romaji;
    }

    public function setNameRomaji(?string $name_romaji): self
    {
        $this->name_romaji = $name_romaji;

        return $this;
    }

    public function getNameJapanese(): ?string
    {
        return $this->name_japanese;
    }

    public function setNameJapanese(?string $name_japanese): self
    {
        $this->name_japanese = $name_japanese;

        return $this;
    }

    public function getCatalognumber(): ?string
    {
        return $this->catalognumber;
    }

    public function setCatalognumber(string $catalognumber): self
    {
        $this->catalognumber = strtoupper($catalognumber);

        return $this;
    }

    /**
     * @return Collection|JavFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(JavFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setTitle($this);
        }

        return $this;
    }

    public function replaceFile(JavFile $file): self
    {
        $matchingRecord = $this->files->filter(
            /** @var JavFile $entry */
            function($entry) use ($file) {
                return $entry->getPath = $file->getPath();
            }
        );

        $this->files->removeElement($matchingRecord->first());
        $this->files->add($file);

        return $this;
    }

    public function removeFile(JavFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getTitle() === $this) {
                $file->setTitle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Model[]
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(Model $model): self
    {
        if (!$this->models->contains($model)) {
            $this->models[] = $model;
        }

        return $this;
    }

    public function removeModel(Model $model): self
    {
        if ($this->models->contains($model)) {
            $this->models->removeElement($model);
        }

        return $this;
    }
}
