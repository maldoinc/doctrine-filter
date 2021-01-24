<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;

class DoctrineFilter
{
    const UNARY_OPS = [
        'is_null' => 'IS NULL',
        'is_not_null' => 'IS NOT NULL'
    ];

    const BINARY_OPS = [
        'gt' => '>',
        'gte' => '>=',
        'eq' => '=',
        'neq' => '!=',
        'lt' => '<',
        'lte' => '<=',
        'in' => 'IN',
        'not_in' => 'NOT IN',
        'contains' => 'like',
        'starts_with' => 'like',
        'ends_with' => 'like'
    ];

    const OPERATIONS = self::BINARY_OPS + self::UNARY_OPS;

    private static function escapeLike($search)
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $search);
    }

    private static function prepareValue($operator, $input)
    {
        if ($operator === 'starts_with') {
            return self::escapeLike($input) . '%';
        }

        if ($operator === 'ends_with') {
            return '%' . self::escapeLike($input);
        }

        if ($operator === 'contains') {
            return '%' . self::escapeLike($input) . '%';
        }

        return $input;
    }

    private static function getRootAlias(QueryBuilder $qb)
    {
        $aliases = $qb->getRootAliases();

        if (!isset($aliases[0])) {
            throw new \Exception('The query builder must contain at least one alias');
        }

        return $aliases[0];
    }

    protected static function applyFiltersFromArray(QueryBuilder $queryBuilder, $filters)
    {
        $alias = self::getRootAlias($queryBuilder);
        $index = 0;

        foreach ($filters as $field => $fieldFilters) {
            if (!is_array($fieldFilters) || $field === 'orderBy') {
                continue;
            }

            foreach ($fieldFilters as $operator => $value) {
                $operator = strtolower($operator);

                if (!in_array($operator, array_keys(self::OPERATIONS))) {
                    throw new InvalidFilterOperatorException(sprintf(
                        "Unknown operator %s. Supported values are %s",
                        $operator,
                        implode(', ', array_keys(self::OPERATIONS))
                    ));
                }

                if (in_array($operator, array_keys(self::BINARY_OPS))) {
                    $dqlOperator = self::BINARY_OPS[$operator];
                    $param_name = "doctrine_filter_{$field}_{$operator}_{$index}";
                    $bindParamString = ($operator === 'in' || $operator === 'not_in')
                        ? "(:$param_name)"
                        : ":$param_name";

                    $queryBuilder
                        ->andWhere(sprintf("$alias.$field $dqlOperator $bindParamString"))
                        ->setParameter($param_name, self::prepareValue($operator, $value));
                } else {
                    $dqlOperator = self::UNARY_OPS[$operator];

                    $queryBuilder->andWhere(sprintf("$alias.$field $dqlOperator"));
                }

                $index++;
            }
        }
    }

    private static function applySortingFromArray(QueryBuilder $queryBuilder, $orderBy)
    {
        $alias = self::getRootAlias($queryBuilder);

        foreach ($orderBy as $field => $direction) {
            $queryBuilder->addOrderBy("$alias.$field", strtolower($direction));
        }
    }

    public static function applyFromArray(QueryBuilder $queryBuilder, $filters)
    {
        if (isset($filters['orderBy'])) {
            self::applySortingFromArray($queryBuilder, $filters['orderBy']);
        }

        self::applyFiltersFromArray($queryBuilder, $filters);
    }

    public static function applyFromQueryString(QueryBuilder $queryBuilder, $queryString)
    {
        parse_str($queryString, $res);
        self::applyFromArray($queryBuilder, $res);
    }
}