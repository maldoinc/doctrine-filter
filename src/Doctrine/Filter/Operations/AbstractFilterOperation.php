<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class AbstractFilterOperation
{
    /** @var callable */
    protected $operationCallback;

    /** @var callable|null */
    protected $valueTransformer;

    /**
     * @param callable $operationCallback callback to use when applying this filter to generate dql
     * @param callable|null $valueTransformer modify the value before it is applied as a parameter
     */
    public function __construct(callable $operationCallback, callable $valueTransformer = null)
    {
        $this->operationCallback = $operationCallback;
        $this->valueTransformer = $valueTransformer;
    }
}
