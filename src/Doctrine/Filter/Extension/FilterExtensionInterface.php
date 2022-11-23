<?php

namespace Maldoinc\Doctrine\Filter\Extension;

use Maldoinc\Doctrine\Filter\Operations\BinaryFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\UnaryFilterOperation;

interface FilterExtensionInterface
{
    /**
     * @return array<string, UnaryFilterOperation>
     */
    public function getUnaryOperators(): array;

    /**
     * @return array<string, BinaryFilterOperation>
     */
    public function getBinaryOperators(): array;
}
