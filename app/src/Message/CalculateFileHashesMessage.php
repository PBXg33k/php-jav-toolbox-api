<?php
namespace App\Message;

class CalculateFileHashesMessage extends JavFileMessage
{
    private $hashFlags;

    public const HASH_MD5    = 1;
    public const HASH_SHA1   = 2;
    public const HASH_SHA512 = 4;
    public const HASH_XXHASH = 8;

    public function __construct(int $javFileId, int $hashMethods = 0)
    {
        $this->hashFlags = $hashMethods;
        parent::__construct($javFileId);
    }

    public function hasMd5() {
        return $this->isFlagSet(self::HASH_MD5);
    }

    public function hasSha1() {
        return $this->isFlagSet(self::HASH_SHA1);
    }

    public function hasSha512() {
        return $this->isFlagSet(self::HASH_SHA512);
    }

    public function hasXxhash() {
        return $this->isFlagSet(self::HASH_XXHASH);
    }

    private function isFlagSet($flag)
    {
        return (($this->hashFlags & $flag) == $flag);
    }
}
