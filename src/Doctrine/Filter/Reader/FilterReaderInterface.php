<?php

namespace Maldoinc\Doctrine\Filter\Reader;

use Maldoinc\Doctrine\Filter\Model\ExposedField;

interface FilterReaderInterface
{
    /**
     * @param class-string[] $classNames
     *
     * @return array<class-string, array<string, ExposedField>>
     */
    public function getExposedFields(array $classNames): array;
}
