<?php

namespace Maldoinc\Doctrine\Filter\Action;

class OrderByAction
{
    private string $field;
    private string $direction;

    public function __construct(string $field, string $direction) {
        $this->field = $field;
        $this->direction = $direction;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
