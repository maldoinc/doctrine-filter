<?php

namespace Maldoinc\Doctrine\Filter\Action;

class FilterAction
{
    public string $publicFieldName;
    public string $operator;

    /** @var mixed|null */
    public $value;

    public function __construct(string $publicFieldName, string $operator, $value = null)
    {
        $this->publicFieldName = $publicFieldName;
        $this->operator = $operator;
        $this->value = $value;
    }
}
