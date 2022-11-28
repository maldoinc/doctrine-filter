<?php

namespace Maldoinc\Doctrine\Filter\Reader;

use Doctrine\Common\Annotations\Reader;

class DoctrineAnnotationReader implements AttributeReaderInterface
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function getPropertyAttributes(\ReflectionProperty $reflectionProperty, string $attributeClass): array
    {
        $attribute = $this->reader->getPropertyAnnotation($reflectionProperty, $attributeClass);

        return $attribute ? [$attribute] : [];
    }
}
