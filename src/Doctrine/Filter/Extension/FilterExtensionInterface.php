<?php

namespace Maldoinc\Doctrine\Filter\Extension;

use Maldoinc\Doctrine\Filter\Operations\AbstractFilterOperation;

interface FilterExtensionInterface
{
    /**
     * @return AbstractFilterOperation[]
     */
    public function getOperators(): array;
}
