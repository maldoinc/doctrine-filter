<?php

namespace Maldoinc\Doctrine\Filter\Operations;

use Doctrine\ORM\Query\Expr;

class UnaryFilterOperation extends AbstractFilterOperation
{
    /**
     * @return Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string
     */
    public function getOperationResult(string $fieldName)
    {
        $callback = $this->operationCallback;

        return $callback($fieldName);
    }
}
