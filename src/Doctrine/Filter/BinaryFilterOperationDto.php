<?php

namespace Maldoinc\Doctrine\Filter;

class BinaryFilterOperationDto
{
    /** @var callable */
    private $operationCallback;

    /** @var callable|null */
    private $valueTransformer;

    public function __construct(callable $operationCallback, callable $valueTransformer = null)
    {
        $this->operationCallback = $operationCallback;
        $this->valueTransformer = $valueTransformer;
    }

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function getOperationResult($left, $right)
    {
        $callback = $this->operationCallback;

        return $callback($left, $right);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function getValue($value)
    {
        $transformer = $this->valueTransformer;

        return is_callable($transformer) ? $transformer($value) : $value;
    }
}