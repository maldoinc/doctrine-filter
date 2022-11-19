<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class BinaryFilterOperation extends AbstractFilterOperation
{
    public function getOperationResult($operator, $value)
    {
        $callback = $this->operationCallback;

        return $callback($operator, $value);
    }
}
