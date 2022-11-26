<?php

namespace Maldoinc\Doctrine\Filter\Extension;

use Doctrine\ORM\Query\Expr;
use Maldoinc\Doctrine\Filter\Operations\BinaryFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\UnaryFilterOperation;

class PresetFilters implements FilterExtensionInterface
{
    public const IS_NULL = 'is_null';
    public const IS_NOT_NULL = 'is_not_null';
    public const GT = 'gt';
    public const GTE = 'gte';
    public const EQ = 'eq';
    public const NEQ = 'neq';
    public const LT = 'lt';
    public const LTE = 'lte';
    public const IN = 'in';
    public const NOT_IN = 'not_in';
    public const STARTS_WITH = 'starts_with';
    public const CONTAINS = 'contains';
    public const ENDS_WITH = 'ends_with';

    public const ALL_PRESETS = [
        self::IS_NULL,
        self::IS_NOT_NULL,
        self::GT,
        self::GTE,
        self::EQ,
        self::NEQ,
        self::LT,
        self::LTE,
        self::IN,
        self::NOT_IN,
        self::STARTS_WITH,
        self::CONTAINS,
        self::ENDS_WITH,
    ];

    public function getUnaryOperators(): array
    {
        return [
            self::IS_NULL => new UnaryFilterOperation(fn ($field) => (new Expr())->isNull($field)),
            self::IS_NOT_NULL => new UnaryFilterOperation(fn ($field) => (new Expr())->isNotNull($field)),
        ];
    }

    public function getBinaryOperators(): array
    {
        $expr = new Expr();

        return [
            self::GT => new BinaryFilterOperation(fn ($field, $val) => $expr->gt($field, $val)),
            self::GTE => new BinaryFilterOperation(fn ($field, $val) => $expr->gte($field, $val)),
            self::EQ => new BinaryFilterOperation(fn ($field, $val) => $expr->eq($field, $val)),
            self::NEQ => new BinaryFilterOperation(fn ($field, $val) => $expr->neq($field, $val)),
            self::LT => new BinaryFilterOperation(fn ($field, $val) => $expr->lt($field, $val)),
            self::LTE => new BinaryFilterOperation(fn ($field, $val) => $expr->lte($field, $val)),
            self::IN => new BinaryFilterOperation(fn ($field, $val) => $expr->in($field, $val)),
            self::NOT_IN => new BinaryFilterOperation(fn ($field, $val) => $expr->notIn($field, $val)),

            self::STARTS_WITH => new BinaryFilterOperation(fn ($field, $val) => $expr->like($field, $val), fn ($value) => $this->escapeLikeWildcards($value) . '%'),

            self::CONTAINS => new BinaryFilterOperation(fn ($field, $val) => $expr->like($field, $val), fn ($value) => '%' . $this->escapeLikeWildcards($value) . '%'),

            self::ENDS_WITH => new BinaryFilterOperation(fn ($field, $val) => $expr->like($field, $val), fn ($value) => '%' . $this->escapeLikeWildcards($value)),
        ];
    }

    private function escapeLikeWildcards(string $search): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $search);
    }
}
