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

    /**
     * @param callable $operationCallback callback to use when applying this filter to generate dql
     * @param callable|null $valueTransformer modify the value before it is applied as a parameter
     * @param callable|null $classMatcher Callback to a function determining if this filter can be applied to the
     *                                    class this entity represents. Use this in combination with any custom
     *                                    filters to have them applied only on the wanted classes.
     */
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
