<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class UnaryFilterOperation extends AbstractFilterOperation
{
    public function getOperationResult($operator)
    {
        $callback = $this->operationCallback;

        return $callback($operator);
    }
}
