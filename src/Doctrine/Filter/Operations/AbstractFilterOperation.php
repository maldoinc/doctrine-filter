<?php

namespace Maldoinc\Doctrine\Filter\Operations;

class AbstractFilterOperation
{
    /** @var callable */
    protected $operationCallback;

    /** @var callable|null */
    protected $valueTransformer;

    /** @var ?callable */
    private $classMatcher;

    public function __construct(
        callable $operationCallback,
        callable $valueTransformer = null,
        ?callable $classMatcher = null
    ) {
        $this->operationCallback = $operationCallback;
        $this->valueTransformer = $valueTransformer;

        // If not specified, filter works on any class.
        $this->classMatcher = $classMatcher;
    }

    /**
     * @param class-string $className
     */
    public function supports(string $className): bool
    {
        $classMatcher = $this->classMatcher;

        return !$classMatcher || $classMatcher($className);
    }
}
