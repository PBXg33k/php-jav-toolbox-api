<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModelRepository")
 */
class Model extends BaseEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_romaji;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_japanese;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ModelAlias", mappedBy="model")
     */
    private $aliases;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Title", mappedBy="models")
     */
    private $titles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Image", mappedBy="models")
     */
    private $images;

    public function __construct()
    {
        $this->aliases = new ArrayCollection();
        $this->titles = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNameRomaji(): ?string
    {
        return $this->name_romaji;
    }

    public function setNameRomaji(string $name_romaji): self
    {
        $this->name_romaji = $name_romaji;

        return $this;
    }

    public function getNameJapanese(): ?string
    {
        return $this->name_japanese;
    }

    public function setNameJapanese(string $name_japanese): self
    {
        $this->name_japanese = $name_japanese;

        return $this;
    }

    /**
     * @return Collection|ModelAlias[]
     */
    public function getAliases(): Collection
    {
        return $this->aliases;
    }

    public function addAlias(ModelAlias $alias): self
    {
        if (!$this->aliases->contains($alias)) {
            $this->aliases[] = $alias;
            $alias->setModel($this);
        }

        return $this;
    }

    public function removeAlias(ModelAlias $alias): self
    {
        if ($this->aliases->contains($alias)) {
            $this->aliases->removeElement($alias);
            // set the owning side to null (unless already changed)
            if ($alias->getModel() === $this) {
                $alias->setModel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Title[]
     */
    public function getTitles(): Collection
    {
        return $this->titles;
    }

    public function addTitle(Title $title): self
    {
        if (!$this->titles->contains($title)) {
            $this->titles[] = $title;
            $title->addModel($this);
        }

        return $this;
    }

    public function removeTitle(Title $title): self
    {
        if ($this->titles->contains($title)) {
            $this->titles->removeElement($title);
            $title->removeModel($this);
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
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
        // @todo serialize aliases, titles and images
        return [
            'id' => $this->id,
            'name_romaji' => $this->name_romaji,
            'name_japanese' => $this->name_japanese,
        ];
    }
}
