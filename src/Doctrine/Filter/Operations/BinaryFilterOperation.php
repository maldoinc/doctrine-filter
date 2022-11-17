<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class BinaryFilterOperation extends AbstractFilterOperation
{
    /**
     * @param mixed $operator
     * @param mixed $value
     *
     * @return mixed
     */
    public function getOperationResult($operator, $value)
    {
        $callback = $this->operationCallback;

        return $callback($operator, $value);
    }
}
