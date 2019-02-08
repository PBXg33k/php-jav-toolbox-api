<?php
namespace App\Model;

class JAVIDExtractionResult
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $release;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $part;

    /**
     * @var string
     */
    private $parser;

    /**
     * @var string
     */
    private $cleanName;

    /**
     * @var \SplFileInfo
     */
    private $fileInfo;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return JAVIDExtractionResult
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
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
     * @return JAVIDExtractionResult
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
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
     * @return JAVIDExtractionResult
     */
    public function setRelease(int $release): self
    {
        $this->release = $release;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return JAVIDExtractionResult
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     * @return JAVIDExtractionResult
     */
    public function setPart(int $part): self
    {
        $this->part = $part;
        return $this;
    }

    /**
     * @return string
     */
    public function getParser(): string
    {
        return $this->parser;
    }

    /**
     * @param string $parser
     * @return JAVIDExtractionResult
     */
    public function setParser(string $parser): self
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @return string
     */
    public function getCleanName(): string
    {
        return $this->cleanName;
    }

    /**
     * @param string $cleanName
     * @return JAVIDExtractionResult
     */
    public function setCleanName(string $cleanName): self
    {
        $this->cleanName = $cleanName;
        return $this;
    }

    /**
     * @return \SplFileInfo
     */
    public function getFileInfo(): \SplFileInfo
    {
        return $this->fileInfo;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @return JAVIDExtractionResult
     */
    public function setFileInfo(\SplFileInfo $fileInfo): self
    {
        $this->fileInfo = $fileInfo;
        return $this;
    }


}
