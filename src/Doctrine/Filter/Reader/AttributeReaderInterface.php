<?php

namespace Maldoinc\Doctrine\Filter\Reader;

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
