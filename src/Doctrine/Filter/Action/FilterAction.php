<?php

namespace Maldoinc\Doctrine\Filter\Action;

class FilterAction
{
    public string $publicFieldName;
    public string $operator;

    /** @var int|string|null */
    public $value;

    /**
     * @param int|string $value
     */
    public function __construct(string $publicFieldName, string $operator, $value = null)
    {
        $this->publicFieldName = $publicFieldName;
        $this->operator = $operator;
        $this->value = $value;
    }
}
