<?php

namespace Maldoinc\Doctrine\Filter\Reader;

use Maldoinc\Doctrine\Filter\Annotation\Expose;
use Maldoinc\Doctrine\Filter\Model\ExposedField;

class ExposedFieldsReader implements FilterReaderInterface
{
    private AttributeReaderInterface $reader;

    public function __construct(AttributeReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @phpstan-param class-string $class
     *
     * @phpstan-return array<string, ExposedField>
     *
     * @throws \Exception
     */
    private function readFieldsFromClass(string $class): array
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $allAttributes = $this->reader->getPropertyAttributes($reflectionProperty, Expose::class);

            if (count($allAttributes) > 1) {
                throw new \Exception(sprintf('Property %s::%s cannot have multiple %s attributes', $class, $reflectionProperty->getName(), Expose::class));
            }

            $attr = current($allAttributes);
            if ($attr instanceof Expose) {
                $serializedName = $attr->serializedName ?: $reflectionProperty->getName();

                $result[$serializedName] = new ExposedField($reflectionProperty->getName(), $attr->operators);
            }
        }

        return $result;
    }

    public function getExposedFields(array $classNames): array
    {
        $res = [];

        foreach ($classNames as $className) {
            $res[$className] = $this->readFieldsFromClass($className);
        }

        return $res;
    }
}
