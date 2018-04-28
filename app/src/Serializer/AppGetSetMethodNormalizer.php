<?php
namespace App\Serializer;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

class AppGetSetMethodNormalizer extends GetSetMethodNormalizer
{
    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {

        parent::__construct($classMetadataFactory, $nameConverter, $propertyTypeExtractor);

        $this->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
    }
}