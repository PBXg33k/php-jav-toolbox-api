<?php
/**
 * Created by PhpStorm.
 * User: PBX_g33k
 * Date: 30/03/2018
 * Time: 14:23
 */

namespace App\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Finder\SplFileInfo;


/**
 * Class JAVTitle
 * @package App\Model
 */
class JAVTitle
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $release;

    /**
     * @var ArrayCollection
     */
    protected $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return JAVTitle
     */
    public function setLabel(string $label): JAVTitle
    {
        $this->label = strtoupper($label);
        return $this;
    }

    /**
     * @return int
     */
    public function getRelease(): int
    {
        return $this->release;
    }

    /**
     * @param int $release
     * @return JAVTitle
     */
    public function setRelease(int $release): JAVTitle
    {
        $this->release = $release;
        return $this;
    }

    public function addFile(JAVFile $file): JAVTitle
    {
        $this->files->add($file);

        return $this;
    }

    public function removeFile(JAVFile $file) : JAVTitle
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function getFiles(): ArrayCollection
    {
        return $this->files;
    }
}