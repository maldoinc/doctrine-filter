<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class BinaryFilterOperation extends AbstractFilterOperation
{
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

    /**
     * @return mixed
     */
    public function getOperationResult(string $fieldName, string $parametrizedValue)
    {
        $callback = $this->operationCallback;

        return $callback($fieldName, $parametrizedValue);
    }
}
