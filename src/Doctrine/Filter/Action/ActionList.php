<?php

namespace Maldoinc\Doctrine\Filter\Action;

use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;

class ActionList
{
    /** @var FilterAction[] */
    private array $filterActions;

    /** @var OrderByAction[] */
    private array $orderByActions;

    /**
     * @param FilterAction[] $filterActions
     * @param OrderByAction[] $orderByActions
     */
    public function __construct(array $filterActions, array $orderByActions)
    {
        $this->filterActions = $filterActions;
        $this->orderByActions = $orderByActions;
    }

    /**
     * Parse a query string while avoiding the replacement of dots into underscores.
     *
     * @return array<string|int, string|int|array<string|int>>
     */
    private static function parseQueryString(string $queryString): array
    {
        /** @var string $queryString */
        $queryString = preg_replace_callback(
            '/(?:^|(?<=&))[^=[]+/',
            fn ($match) => bin2hex(urldecode($match[0])),
            $queryString
        );

        parse_str($queryString, $result);

        /* @phpstan-ignore-next-line */
        return array_combine(array_map('hex2bin', array_keys($result)), $result);
    }

    /**
     * Parse the query string and create an instance of this class
     * with any filter and sort actions found in it.
     *
     * @param ?string $orderByKey The name under which to look for sort actions
     * @param bool $simpleEquality interpret field=value as an equality operation (same as field[eq]=value)
     *
     * @see parse_str
     */
    public static function fromQueryString(
        string $queryString,
        ?string $orderByKey = null,
        bool $simpleEquality = false
    ): self {
        /* @phpstan-ignore-next-line */
        return self::fromArray(static::parseQueryString($queryString), $orderByKey, $simpleEquality);
    }

    /**
     * Collects filter and orderBy actions from the data and creates a new instance of this class.
     *
     * **WARNING**: If using symfony or HttpFoundation, DO NOT PASS `$request->query->all()` here if you are using join
     * filter syntax. Due to known behavior in php's `parse_str`, it will convert dots in the query string to
     * underscores which is unwanted.
     *
     * Instead, do one of the following:
     * - Use `fromQueryString($request->server->get('QUERY_STRING'))` and let this library handle the parsing. DO NOT
     * use `$request->getQueryString()` as it won't work.
     * - Use Symfony's HeaderUtils as follows. `HeaderUtils::parseQuery($request->getQueryString()` to generate the
     * data array and pass that instead.
     *
     * @see parse_str
     * @see https://www.php.net/manual/en/function.parse-str.php
     * @see https://github.com/symfony/symfony/issues/29664
     * @see https://github.com/symfony/symfony/pull/37272
     *
     * @param array<string, string|array<string, string|int>> $data
     * @param bool $simpleEquality interpret data in the format `key => scalar value` as an equality operation
     */
    public static function fromArray(array $data, ?string $orderByKey = null, bool $simpleEquality = false): self
    {
        $filterActions = [];
        $orderByActions = [];

        if ($orderByKey && isset($data[$orderByKey]) && is_array($data[$orderByKey])) {
            foreach ($data[$orderByKey] as $field => $direction) {
                if (is_string($direction) && in_array(strtolower($direction), ['asc', 'desc'])) {
                    $orderByActions[] = new OrderByAction($field, $direction);
                }
            }
        }

        foreach ($data as $field => $fieldFilters) {
            if (is_array($fieldFilters)) {
                foreach ($fieldFilters as $operator => $value) {
                    $filterActions[] = new FilterAction($field, $operator, $value);
                }
            } elseif ($simpleEquality) {
                $filterActions[] = new FilterAction($field, PresetFilterProvider::EQ, $fieldFilters);
            }
        }

        return new self($filterActions, $orderByActions);
    }

    /**
     * @return OrderByAction[]
     */
    public function getOrderByActions(): array
    {
        return $this->orderByActions;
    }

    /**
     * @return FilterAction[]
     */
    public function getFilterActions(): array
    {
        return $this->filterActions;
    }
}
