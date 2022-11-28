<?php

namespace Maldoinc\Doctrine\Filter\Operations;

use Doctrine\ORM\Query\Expr;

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
     * @return Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string
     */
    public function getOperationResult(string $fieldName, string $parametrizedValue)
    {
        $callback = $this->operationCallback;

        return $callback($fieldName, $parametrizedValue);
    }
}
