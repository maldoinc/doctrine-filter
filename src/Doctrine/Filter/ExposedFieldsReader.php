<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\Common\Annotations\AnnotationReader;
use Maldoinc\Doctrine\Filter\Annotation\Expose;

class ExposedFieldsReader
{
    public static function readExposedFields(string $class)
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($class);
        $reader = new AnnotationReader();

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            /** @var Expose $exposeAnnotation */
            $exposeAnnotation = $reader->getPropertyAnnotation($reflectionProperty, Expose::class);

            if ($exposeAnnotation) {
                $serializedName = $exposeAnnotation->serializedName ?: $reflectionProperty->getName();

                $result[$serializedName] = $reflectionProperty->getName();
            }
        }

        return $result;
    }
}