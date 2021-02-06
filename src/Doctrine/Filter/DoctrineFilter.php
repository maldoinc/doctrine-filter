<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;

class DoctrineFilter
{
    private $queryBuilder;
    private $parameterIndex = 0;
    private $rootAlias;

    private $unaryOps;
    private $binaryOps;
    private $ops;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->rootAlias = $this->getRootAlias();

        $this->initializeOperations();
    }

    public function applyFromQueryString($queryString, $exposedFields)
    {
        parse_str($queryString, $res);
        $this->applyFromArray($res, $exposedFields);
    }

    public function applyFromArray($filters, $exposedFields)
    {
        if (isset($filters['orderBy'])) {
            $this->applySortingFromArray($filters['orderBy']);
        }

        $this->applyFiltersFromArray($filters, $exposedFields);
    }

    private function getRootAlias()
    {
        $aliases = $this->queryBuilder->getRootAliases();

        if (!isset($aliases[0])) {
            throw new EmptyQueryBuilderException('Query builder must contain at least one alias');
        }

        return $aliases[0];
    }

    private function initializeOperations()
    {
        $this->binaryOps = [
            'gt' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->gt($field, $val);
            }),

            'gte' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->gte($field, $val);
            }),

            'eq' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->eq($field, $val);
            }),

            'neq' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->neq($field, $val);
            }),

            'lt' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->lt($field, $val);
            }),

            'lte' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->lte($field, $val);
            }),

            'in' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->in($field, $val);
            }),

            'not_in' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->notIn($field, $val);
            }),

            'starts_with' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->like($field, $val);
            }, function ($value) {
                return $this->escapeLikeWildcards($value) . '%';
            }),

            'contains' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->like($field, $val);
            }, function ($value) {
                return '%' . $this->escapeLikeWildcards($value) . '%';
            }),

            'ends_with' => new BinaryFilterOperationDto(function ($field, $val) {
                return $this->queryBuilder->expr()->like($field, $val);
            }, function ($value) {
                return '%' . $this->escapeLikeWildcards($value);
            }),
        ];

        $this->unaryOps = [
            'is_null' => function ($field) {
                return $this->queryBuilder->expr()->isNull($field);
            },
            'is_not_null' => function ($field) {
                return $this->queryBuilder->expr()->isNotNull($field);
            }
        ];

        $this->ops = $this->binaryOps + $this->unaryOps;
    }

    private function escapeLikeWildcards($search)
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $search);
    }

    private function applySortingFromArray($orderBy)
    {
        foreach ($orderBy as $field => $direction) {
            $this->queryBuilder->addOrderBy("{$this->rootAlias}.$field", strtolower($direction));
        }
    }

    private function applyFiltersFromArray($filters, $exposedFields)
    {
        $this->parameterIndex = 0;

        foreach ($filters as $field => $fieldFilters) {
            if (!is_array($fieldFilters) || $field === 'orderBy' || !array_key_exists($field, $exposedFields)) {
                continue;
            }

            foreach ($fieldFilters as $operator => $value) {
                $operator = strtolower($operator);
                $dqlField = $exposedFields[$field];

                if (!in_array($operator, array_keys($this->ops))) {
                    throw new InvalidFilterOperatorException(sprintf(
                        "Unknown operator %s. Supported values are %s",
                        $operator,
                        implode(', ', array_keys($this->ops))
                    ));
                }

                if (in_array($operator, array_keys($this->binaryOps))) {
                    $this->applyBinaryFilter($dqlField, $operator, $value);
                } else {
                    $this->applyUnaryFilter($dqlField, $operator);
                }
            }
        }
    }

    private function getNextParameterName($field, $operator)
    {
        $paramName = "doctrine_filter_{$field}_{$operator}_{$this->parameterIndex}";
        $this->parameterIndex++;

        return $paramName;
    }

    private function applyBinaryFilter(string $field, string $operator, $value)
    {
        $paramName = $this->getNextParameterName($field, $operator);
        $operation = $this->binaryOps[$operator];
        $aliasedFieldName = sprintf("%s.%s", $this->rootAlias, $field);

        $this->queryBuilder
            ->andWhere($operation->getOperationResult($aliasedFieldName, ":$paramName"))
            ->setParameter($paramName, $operation->getValue($value));
    }

    private function applyUnaryFilter(string $field, string $operator)
    {
        $this->queryBuilder->andWhere($this->unaryOps[$operator](sprintf("%s.%s", $this->rootAlias, $field)));
    }
}