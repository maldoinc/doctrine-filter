<?php

namespace Maldoinc\Doctrine\Filter\Action;

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
     * Parse the query string using php's `parse_str` method and create an instance of this class
     * with any filter and sort actions found in it.
     *
     * @param ?string $orderByKey The name under which to look for sort actions
     *
     * @see parse_str
     */
    public static function fromQueryString(string $queryString, ?string $orderByKey = null): self
    {
        parse_str($queryString, $queryData);

        /* @phpstan-ignore-next-line */
        return self::fromArray($queryData, $orderByKey);
    }

    /**
     * @param array<string, string|array<string, string|int>> $data
     */
    public static function fromArray(array $data, ?string $orderByKey = null): self
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
