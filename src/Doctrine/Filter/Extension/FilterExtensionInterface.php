<?php

namespace Maldoinc\Doctrine\Filter\Extension;

use Maldoinc\Doctrine\Filter\Operations\BinaryFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\UnaryFilterOperation;

interface FilterExtensionInterface
{
    /**
     * @return UnaryFilterOperation[]
     */
    public function getUnaryOperators(): array;

    /**
     * @return BinaryFilterOperation[]
     */
    public function getBinaryOperators(): array;


}