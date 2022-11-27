<?php

namespace Maldoinc\Doctrine\Filter\Provider;

use Maldoinc\Doctrine\Filter\Operations\AbstractFilterOperation;

interface FilterProviderInterface
{
    /**
     * @return AbstractFilterOperation[]
     */
    public function getOperators(): array;
}
