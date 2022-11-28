<?php

namespace Maldoinc\Doctrine\Filter\Reader\AttributeReader;

class NativeAttributeReader implements AttributeReaderInterface
{
    public function getPropertyAttributes(\ReflectionProperty $reflectionProperty, string $attributeClass): array
    {
        if (PHP_MAJOR_VERSION < 8) {
            throw new \Exception(sprintf('%s is not supported for PHP versions < 8', self::class));
        }

        return array_map(fn (\ReflectionAttribute $attribute) => $attribute->newInstance(), $reflectionProperty->getAttributes($attributeClass, \ReflectionAttribute::IS_INSTANCEOF));
    }
}
