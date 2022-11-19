<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class UnaryFilterOperation extends AbstractFilterOperation
{
    public function getOperationResult(string $fieldName)
    {
        $callback = $this->operationCallback;

        return $callback($fieldName);
    }
}
