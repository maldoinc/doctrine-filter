<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class UnaryFilterOperation extends AbstractFilterOperation
{
    /**
     * @param mixed $operator
     *
     * @return mixed
     */
    public function getOperationResult($operator)
    {
        $callback = $this->operationCallback;

        return $callback($operator);
    }
}
