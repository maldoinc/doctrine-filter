<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Annotation\Expose;
use Maldoinc\Doctrine\Filter\Reader\AttributeReaderInterface;

class ExposedFieldsReader
{
    private AttributeReaderInterface $reader;

    public function __construct(AttributeReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @phpstan-return array<class-string, array<string, ExposedField>>
     */
    public function readExposedFields(QueryBuilder $queryBuilder): array
    {
        $res = [];

        foreach ($queryBuilder->getRootEntities() as $entity) {
            if (class_exists($entity)) {
                $res[$entity] = $this->readFieldsFromClass($entity);
            }
        }

        return $res;
    }

    /**
     * @phpstan-param class-string $class
     * @phpstan-return array<string, ExposedField>
     *
     * @throws \Exception
     */
    private function readFieldsFromClass(string $class): array
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $allAttributes = $this->reader->getAttributes($reflectionProperty, Expose::class);

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
}
