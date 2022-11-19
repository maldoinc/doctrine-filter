<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class BinaryFilterOperation extends AbstractFilterOperation
{
    public function getOperationResult(string $fieldName, string $parametrizedValue)
    {
        $callback = $this->operationCallback;

        return $callback($fieldName, $parametrizedValue);
    }
}
