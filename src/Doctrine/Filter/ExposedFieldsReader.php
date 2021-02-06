<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\Common\Annotations\AnnotationReader;
use Maldoinc\Doctrine\Filter\Annotation\Expose;

class ExposedFieldsReader
{
    /**
     * @psalm-param class-string $class
     * @psalm-return array<string, string>
     * @return array
     */
    public static function readExposedFields(string $class)
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($class);
        $reader = new AnnotationReader();

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $exposeAnnotation = $reader->getPropertyAnnotation($reflectionProperty, Expose::class);

            if ($exposeAnnotation instanceof Expose) {
                $serializedName = $exposeAnnotation->serializedName ?: $reflectionProperty->getName();

                $result[$serializedName] = $reflectionProperty->getName();
            }
        }

        return $result;
    }
}