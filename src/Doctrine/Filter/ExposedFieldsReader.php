<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Annotation\Expose;

class ExposedFieldsReader
{
    /** @var Reader */
    private $reader;

    public function __construct(Reader $reader)
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
            /** @var class-string $entity */
            $res[$entity] = $this->readFieldsFromClass($entity);
        }

        return $res;
    }

    /**
     * @phpstan-param class-string $class
     * @phpstan-return array<string, ExposedField>
     */
    private function readFieldsFromClass(string $class): array
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $exposeAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Expose::class);

            if ($exposeAnnotation instanceof Expose) {
                $serializedName = $exposeAnnotation->serializedName ?: $reflectionProperty->getName();

                $result[$serializedName] = new ExposedField(
                    $reflectionProperty->getName(),
                    $exposeAnnotation->operators
                );
            }
        }

        return $result;
    }
}
