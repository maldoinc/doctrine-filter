<?php

namespace Maldoinc\Doctrine\Filter\Reader\AttributeReader;

interface AttributeReaderInterface
{
    /**
     * @template T
     *
     * @param class-string<T> $attributeClass
     *
     * @return T[]
     */
    public function getPropertyAttributes(\ReflectionProperty $reflectionProperty, string $attributeClass): array;
}
