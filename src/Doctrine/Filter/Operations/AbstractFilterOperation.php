<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class AbstractFilterOperation
{
    /** @var callable */
    protected $operationCallback;

    /** @var callable|null */
    protected $valueTransformer;

    public function __construct(callable $operationCallback, callable $valueTransformer = null)
    {
        $this->operationCallback = $operationCallback;
        $this->valueTransformer = $valueTransformer;
    }

}
