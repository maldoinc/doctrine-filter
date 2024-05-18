<?php

namespace Maldoinc\Doctrine\Filter\Model;

class ExposedField
{
    private string $fieldName;

    /** @var string[] */
    private array $operators;
    private string $className;

    /**
     * @param class-string $className
     * @param string[] $operators
     */
    public function __construct(string $className, string $fieldName, array $operators)
    {
        $this->fieldName = $fieldName;
        $this->operators = $operators;
        $this->className = $className;
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

    public function getClassName(): string
    {
        return $this->className;
    }
}
