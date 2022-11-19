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
        self::ENDS_WITH
    ];

    public function getUnaryOperators(): array
    {
        return [
            self::IS_NULL => new UnaryFilterOperation(function ($field) {
                return (new Expr())->isNull($field);
            }),
            self::IS_NOT_NULL => new UnaryFilterOperation(function ($field) {
                return (new Expr())->isNotNull($field);
            }),
        ];
    }

    public function getBinaryOperators(): array
    {
        return [
            self::GT => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->gt($field, $val);
            }),

            self::GTE => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->gte($field, $val);
            }),

            self::EQ => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->eq($field, $val);
            }),

            self::NEQ => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->neq($field, $val);
            }),

            self::LT => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->lt($field, $val);
            }),

            self::LTE => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->lte($field, $val);
            }),

            self::IN => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->in($field, $val);
            }),

            self::NOT_IN => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->notIn($field, $val);
            }),

            self::STARTS_WITH => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->like($field, $val);
            }, function ($value) {
                return $this->escapeLikeWildcards($value).'%';
            }),

            self::CONTAINS => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->like($field, $val);
            }, function ($value) {
                return '%'.$this->escapeLikeWildcards($value).'%';
            }),

            self::ENDS_WITH => new BinaryFilterOperation(function ($field, $val) {
                return (new Expr())->like($field, $val);
            }, function ($value) {
                return '%'.$this->escapeLikeWildcards($value);
            }),
        ];
    }

    private function escapeLikeWildcards(string $search): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $search);
    }
}
