<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class UnaryFilterOperation extends AbstractFilterOperation
{
    /**
     * @return mixed
     */
    public function getOperationResult(string $fieldName)
    {
        $callback = $this->operationCallback;

        return $callback($fieldName);
    }
}
