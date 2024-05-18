<?php

namespace Maldoinc\Doctrine\Filter\Action;

class FilterAction
{
    private ?string $entityAlias = null;
    private string $publicFieldName;
    private string $operator;

    /** @var int|string|null */
    public $value;

    /**
     * @param int|string $value
     */
    public function __construct(string $publicFieldName, string $operator, $value = null)
    {
        $this->parsePublicFieldName($publicFieldName);
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Check if the public name uses a dot notation and if so set both the public field name as well as the alias.
     */
    private function parsePublicFieldName(string $publicFieldName): void
    {
        $parts = explode('.', $publicFieldName, 2);

        if (1 === count($parts)) {
            $this->publicFieldName = $publicFieldName;

            return;
        }

        [$this->entityAlias, $this->publicFieldName] = $parts;
    }

    public function getEntityAlias(): ?string
    {
        return $this->entityAlias;
    }

    public function getPublicFieldName(): string
    {
        return $this->publicFieldName;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
