<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use Maldoinc\Doctrine\Filter\Extension\FilterExtensionInterface;
use Maldoinc\Doctrine\Filter\Operations\BinaryFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\UnaryFilterOperation;

class DoctrineFilter
{
    /** @var QueryBuilder */
    private $queryBuilder;

    /** @var int */
    private $parameterIndex = 0;

    /** @var string */
    private $rootAlias;

    /** @var array<string, UnaryFilterOperation|BinaryFilterOperation> */
    private $ops = [];

    /** @var array<class-string, array<string, string>> */
    private $exposedFields;

    /**
     * @phpstan-param array<class-string, array<string, string>> $exposedFields
     *
     * @param FilterExtensionInterface[] $extensions
     *
     * @throws EmptyQueryBuilderException
     */
    public function __construct(QueryBuilder $queryBuilder, array $exposedFields, array $extensions)
    {
        $this->queryBuilder = $queryBuilder;
        $this->rootAlias = $this->getRootAlias();
        $this->exposedFields = $exposedFields;

        $this->initializeOperations($extensions);
    }

    /**
     * @throws InvalidFilterOperatorException
     */
    public function applyFromQueryString(string $queryString): void
    {
        parse_str($queryString, $res);
        $this->applyFromArray($res);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @throws InvalidFilterOperatorException
     */
    public function applyFromArray(array $filters): void
    {
        if (isset($filters['orderBy'])) {
            $this->applySortingFromArray($filters['orderBy']);
        }

        $this->applyFiltersFromArray($filters);
    }

    /**
     * @throws EmptyQueryBuilderException
     */
    private function getRootAlias(): string
    {
        $aliases = $this->queryBuilder->getRootAliases();

        if (!isset($aliases[0])) {
            throw new EmptyQueryBuilderException('Query builder must contain at least one alias');
        }

        return $aliases[0];
    }

    /**
     * @param FilterExtensionInterface[] $extensions
     */
    private function initializeOperations(array $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->ops = array_merge($this->ops, $extension->getUnaryOperators(), $extension->getBinaryOperators());
        }
    }

    /**
     * @param array<string, string> $orderBy
     */
    private function applySortingFromArray(array $orderBy): void
    {
        foreach ($orderBy as $field => $direction) {
            $this->queryBuilder->addOrderBy("$this->rootAlias.$field", strtolower($direction));
        }
    }

    /**
     * @param array<string, array<string, string>> $filters
     *
     * @throws InvalidFilterOperatorException
     */
    private function applyFiltersFromArray(array $filters): void
    {
        $this->parameterIndex = 0;
        $exposedFields = $this->exposedFields[$this->queryBuilder->getRootEntities()[0]];

        foreach ($filters as $field => $fieldFilters) {
            if (!is_array($fieldFilters) || 'orderBy' === $field || !array_key_exists($field, $exposedFields)) {
                continue;
            }

            foreach ($fieldFilters as $operator => $value) {
                $operator = strtolower($operator);
                $dqlField = $exposedFields[$field];

                if (!in_array($operator, array_keys($this->ops))) {
                    throw new InvalidFilterOperatorException(sprintf('Unknown operator %s. Supported values are %s', $operator, implode(', ', array_keys($this->ops))));
                }

                $operation = $this->ops[$operator];

                if ($operation instanceof BinaryFilterOperation) {
                    $this->applyBinaryFilter($dqlField, $operator, $operation, $value);
                } elseif ($operation instanceof UnaryFilterOperation) {
                    $this->applyUnaryFilter($dqlField, $operation);
                }
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function applyBinaryFilter(string $field, string $operator, BinaryFilterOperation $operation, $value): void
    {
        $paramName = $this->getNextParameterName($field, $operator);
        $aliasedFieldName = sprintf('%s.%s', $this->rootAlias, $field);

        $this->queryBuilder
            ->andWhere($operation->getOperationResult($aliasedFieldName, ":$paramName"))
            ->setParameter($paramName, $operation->getValue($value));
    }

    private function getNextParameterName(string $field, string $operator): string
    {
        $paramName = "doctrine_filter_{$field}_{$operator}_$this->parameterIndex";
        ++$this->parameterIndex;

        return $paramName;
    }

    private function applyUnaryFilter(string $field, UnaryFilterOperation $operation): void
    {
        $this->queryBuilder->andWhere($operation->getOperationResult(sprintf('%s.%s', $this->rootAlias, $field)));
    }
}
