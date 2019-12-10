<?php

namespace App\Entity;

use Pbxg33k\Traits\HydratableTrait;

abstract class BaseEntity implements \JsonSerializable
{
    use HydratableTrait;

    /**
     * @param string $jsonData
     * @return BaseEntity
     * @throws \JsonException if data is not decodable
     */
    public static function JsonDecode(string $jsonData)
    {
        $decodedArray = json_decode($jsonData, true);
        if(json_last_error()) {
            throw new \JsonException(json_last_error_msg(), json_last_error());
        }

        $object = new static();

        foreach($decodedArray as $key => $value) {
            $setter = sprintf("set%s", ucfirst($key));
            if(method_exists($object, $setter)) {
                $object->{$setter}($value);
            }
        }

        return $object;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public abstract function jsonSerialize();
}
