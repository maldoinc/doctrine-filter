<?php

namespace Maldoinc\Doctrine\Filter;

class ExposedField
{
    /** @var string */
    private $fieldName;

    /** @var string[] */
    private $operators;

    /**
     * @param string[] $operators
     */
    public function __construct(string $fieldName, array $operators)
    {
        $this->fieldName = $fieldName;
        $this->operators = $operators;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string[]
     */
    public function getOperators(): array
    {
        return $this->operators;
    }
}
