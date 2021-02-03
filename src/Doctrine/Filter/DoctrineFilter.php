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

    private $queryBuilder;
    private $parameterIndex = 0;
    private $rootAlias;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->rootAlias = $this->getRootAlias();
    }

    private function escapeLike($search)
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $search);
    }

    private function prepareValue($operator, $input)
    {
        if ($operator === 'starts_with') {
            return $this->escapeLike($input) . '%';
        }

        if ($operator === 'ends_with') {
            return '%' . $this->escapeLike($input);
        }

        if ($operator === 'contains') {
            return '%' . $this->escapeLike($input) . '%';
        }

        return $input;
    }

    private function getRootAlias()
    {
        $aliases = $this->queryBuilder->getRootAliases();

        if (!isset($aliases[0])) {
            throw new \Exception('The query builder must contain at least one alias');
        }

        return $aliases[0];
    }

    protected function applyFiltersFromArray($filters)
    {
        $this->parameterIndex = 0;

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
                    $this->applyBinaryFilter($field, $operator, $value);
                } else {
                    $this->applyUnaryFilter($field, $operator);
                }

                $this->parameterIndex++;
            }
        }
    }

    private function applyUnaryFilter(string $field, string $operator)
    {
        $dqlOperator = self::UNARY_OPS[$operator];

        $this->queryBuilder->andWhere(sprintf("{$this->rootAlias}.$field $dqlOperator"));
    }

    private function applyBinaryFilter(string $field, string $operator, $value)
    {
        $alias = $this->rootAlias;
        $dqlOperator = self::BINARY_OPS[$operator];
        $param_name = "doctrine_filter_{$field}_{$operator}_{$this->parameterIndex}";
        $bindParamString = ($operator === 'in' || $operator === 'not_in')
            ? "(:$param_name)"
            : ":$param_name";

        $this->queryBuilder
            ->andWhere(sprintf("$alias.$field $dqlOperator $bindParamString"))
            ->setParameter($param_name, $this->prepareValue($operator, $value));
    }

    private function applySortingFromArray($orderBy)
    {
        foreach ($orderBy as $field => $direction) {
            $this->queryBuilder->addOrderBy("{$this->rootAlias}.$field", strtolower($direction));
        }
    }

    public function applyFromArray($filters)
    {
        if (isset($filters['orderBy'])) {
            $this->applySortingFromArray($filters['orderBy']);
        }

        $this->applyFiltersFromArray($filters);
    }

    public function applyFromQueryString($queryString)
    {
        parse_str($queryString, $res);
        $this->applyFromArray($res);
    }
}