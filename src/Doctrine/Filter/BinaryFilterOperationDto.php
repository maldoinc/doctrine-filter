<?php

namespace Maldoinc\Doctrine\Filter;

class BinaryFilterOperationDto
{
    private $operationCallback;
    private $valueTransformer;

    public function __construct(callable $operationCallback, callable $valueTransformer = null)
    {
        $this->operationCallback = $operationCallback;
        $this->valueTransformer = $valueTransformer;
    }

    public function getOperationResult($left, $right)
    {
        $callback = $this->operationCallback;

        return $callback($left, $right);
    }

    public function getValue($value)
    {
        $transformer = $this->valueTransformer;

        return is_callable($this->valueTransformer) ? $transformer($value) : $value;
    }
}