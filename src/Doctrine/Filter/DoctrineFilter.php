<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\Action\FilterAction;
use Maldoinc\Doctrine\Filter\Action\OrderByAction;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use Maldoinc\Doctrine\Filter\Extension\FilterExtensionInterface;
use Maldoinc\Doctrine\Filter\Operations\BinaryFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\UnaryFilterOperation;

class DoctrineFilter
{
    private QueryBuilder $queryBuilder;

    private int $parameterIndex = 0;

    private string $rootAlias;

    /** @var array<string, UnaryFilterOperation|BinaryFilterOperation> */
    private array $ops = [];

    /** @var array<class-string, array<string, ExposedField>> */
    private array $exposedFields;

    /**
     * @param array<class-string, array<string, ExposedField>> $exposedFields
     * @param FilterExtensionInterface[] $extensions
     *
     * @throws EmptyQueryBuilderException
     */
    public function __construct(QueryBuilder $queryBuilder, array $exposedFields, array $extensions)
    {
        $this->queryBuilder = $queryBuilder;
        $this->rootAlias = $this->getRootAlias();
        $this->exposedFields = $exposedFields;

        $this->initializeOperations($extensions);
    }

    /**
     * @throws InvalidFilterOperatorException
     * @throws EmptyQueryBuilderException
     */
    public function apply(ActionList $actionSet): void
    {
        $this->applySorting($actionSet->getOrderByActions());
        $this->applyFilters($actionSet->getFilterActions());
    }

    /**
     * @throws EmptyQueryBuilderException
     */
    private function getRootAlias(): string
    {
        $aliases = $this->queryBuilder->getRootAliases();

        if (!isset($aliases[0])) {
            throw new EmptyQueryBuilderException('Query builder must contain at least one alias');
        }

        return $aliases[0];
    }

    /**
     * @param FilterExtensionInterface[] $extensions
     */
    private function initializeOperations(array $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->ops = array_merge($this->ops, $extension->getUnaryOperators(), $extension->getBinaryOperators());
        }
    }

    /**
     * @param OrderByAction[] $orderBy ;
     *
     * @throws EmptyQueryBuilderException
     */
    private function applySorting(array $orderBy): void
    {
        foreach ($orderBy as $value) {
            $this->queryBuilder->addOrderBy(
                sprintf('%s.%s', $this->getRootAlias(), $value->getField()),
                $value->getDirection()
            );
        }
    }

    /**
     * @param FilterAction[] $filters
     *
     * @throws InvalidFilterOperatorException
     */
    private function applyFilters(array $filters): void
    {
        $this->parameterIndex = 0;

        /** @var class-string $rootEntity */
        $rootEntity = $this->queryBuilder->getRootEntities()[0];
        $exposedFields = $this->exposedFields[$rootEntity];

        foreach ($filters as $filterAction) {
            if (!array_key_exists($filterAction->publicFieldName, $exposedFields)) {
                continue;
            }

            $exposedField = $exposedFields[$filterAction->publicFieldName];
            $operator = $filterAction->operator;

            if (!(isset($this->ops[$operator]) && in_array($operator, $exposedField->getOperators()))) {
                $supportedFields = implode(
                    ', ',
                    array_intersect(array_keys($this->ops), $exposedField->getOperators())
                );

                $message = sprintf('Unknown operator "%s". Supported values for field %s are: [%s]',
                    $operator,
                    $filterAction->publicFieldName,
                    $supportedFields
                );

                throw new InvalidFilterOperatorException($message);
            }

            $operation = $this->ops[$operator];

            if (!$operation->supports($rootEntity)) {
                throw new InvalidFilterOperatorException(sprintf('Operator "%s" not supported for this resource', $operator));
            }

            if ($operation instanceof BinaryFilterOperation) {
                $this->applyBinaryFilter($exposedField->getFieldName(), $operator, $operation, $filterAction->value);
            } elseif ($operation instanceof UnaryFilterOperation) {
                $this->applyUnaryFilter($exposedField->getFieldName(), $operation);
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function applyBinaryFilter(string $field, string $operator, BinaryFilterOperation $operation, $value): void
    {
        $paramName = $this->getNextParameterName($field, $operator);
        $aliasedFieldName = sprintf('%s.%s', $this->rootAlias, $field);

        $this->queryBuilder
            ->andWhere($operation->getOperationResult($aliasedFieldName, ":$paramName"))
            ->setParameter($paramName, $operation->getValue($value));
    }

    private function getNextParameterName(string $field, string $operator): string
    {
        $paramName = "doctrine_filter_{$field}_{$operator}_$this->parameterIndex";
        ++$this->parameterIndex;

        return $paramName;
    }

    private function applyUnaryFilter(string $field, UnaryFilterOperation $operation): void
    {
        $this->queryBuilder->andWhere($operation->getOperationResult(sprintf('%s.%s', $this->rootAlias, $field)));
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
