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

    public static function fromQueryString(string $queryString, string $orderByKey = null): self
    {
        $filterActions = [];
        $orderByActions = [];

        parse_str($queryString, $res);

        if ($orderByKey && isset($res[$orderByKey]) && is_array($res[$orderByKey])) {
            foreach ($res[$orderByKey] as $field => $direction) {
                /*
                 * @phpstan-ignore-next-line Phpstan gets confused about $field being an int
                 */
                $orderByActions[] = new OrderByAction($field, $direction);
            }
        }

        foreach ($res as $field => $fieldFilters) {
            if (is_array($fieldFilters)) {
                foreach ($fieldFilters as $operator => $value) {
                    /*
                     * @phpstan-ignore-next-line Phpstan gets confused about $field being an int
                     */
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
