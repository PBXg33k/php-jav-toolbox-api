<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModelAliasRepository")
 */
class ModelAlias
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Model", inversedBy="aliases")
     */
    private $model;

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

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function setModel(?Model $model): self
    {
        $this->model = $model;

        return $this;
    }
}
