<?php

namespace Maldoinc\Doctrine\Filter;

class ExposedField
{
    private string $fieldName;

    /** @var string[] */
    private array $operators;

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
