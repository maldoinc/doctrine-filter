<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class AbstractFilterOperation
{
    /** @var callable */
    protected $operationCallback;

    /** @var callable|null */
    private $valueTransformer;

    public function __construct(callable $operationCallback, callable $valueTransformer = null)
    {
        $this->operationCallback = $operationCallback;
        $this->valueTransformer = $valueTransformer;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getValue($value)
    {
        $transformer = $this->valueTransformer;

        return is_callable($transformer) ? $transformer($value) : $value;
    }
}
