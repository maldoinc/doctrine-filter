<?php

namespace Maldoinc\Doctrine\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\Action\FilterAction;
use Maldoinc\Doctrine\Filter\Action\OrderByAction;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use Maldoinc\Doctrine\Filter\Model\ExposedField;
use Maldoinc\Doctrine\Filter\Model\QueryBuilderMetadata;
use Maldoinc\Doctrine\Filter\Operations\AbstractFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\BinaryFilterOperation;
use Maldoinc\Doctrine\Filter\Operations\UnaryFilterOperation;
use Maldoinc\Doctrine\Filter\Provider\FilterProviderInterface;
use Maldoinc\Doctrine\Filter\QueryBuilder\QueryBuilderMetadataReader;
use Maldoinc\Doctrine\Filter\Reader\FilterReaderInterface;

class DoctrineFilter
{
    private QueryBuilder $queryBuilder;
    private QueryBuilderMetadata $queryBuilderMetadata;

    private int $parameterIndex = 0;

    /** @var array<string, AbstractFilterOperation> */
    private array $ops = [];

    /** @var array<class-string, array<string, ExposedField>> */
    private array $exposedFields;

    /** @var array<Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string> */
    private array $expressions = [];

    /**
     * @param FilterProviderInterface[] $filterProviders
     *
     * @throws EmptyQueryBuilderException
     */
    public function __construct(QueryBuilder $queryBuilder, FilterReaderInterface $filterReader, array $filterProviders)
    {
        $this->queryBuilderMetadata = QueryBuilderMetadataReader::getMetadata($queryBuilder);
        $this->queryBuilder = $queryBuilder;
        $this->exposedFields = $filterReader->getExposedFields(
            array_values($this->queryBuilderMetadata->getAliasToEntityMap())
        );

        $this->initializeOperations($filterProviders);
    }

    /**
     * @throws InvalidFilterOperatorException
     */
    public function apply(ActionList $actions): void
    {
        $this->applySorting($actions->getOrderByActions());
        $this->applyFilters($actions->getFilterActions());
    }

    /**
     * @param FilterProviderInterface[] $filterProviders
     */
    private function initializeOperations(array $filterProviders): void
    {
        foreach ($filterProviders as $provider) {
            $this->ops = array_merge($this->ops, $provider->getOperators());
        }
    }

    /**
     * @param OrderByAction[] $orderBy
     */
    private function applySorting(array $orderBy): void
    {
        foreach ($orderBy as $value) {
            $this->queryBuilder->addOrderBy(
                sprintf('%s.%s', $this->queryBuilderMetadata->getRootAlias(), $value->getField()),
                $value->getDirection()
            );
        }
    }

    /**
     * @param FilterAction[] $filterActions
     *
     * @throws InvalidFilterOperatorException
     */
    private function applyFilters(array $filterActions): void
    {
        foreach ($filterActions as $action) {
            $exposedField = $this->getExposedFieldForAction($action);

            // Field is not mapped
            if (!$exposedField) {
                continue;
            }

            $operation = $this->getOperation($action, $exposedField);

            if ($operation instanceof BinaryFilterOperation) {
                $this->applyBinaryFilter($exposedField, $action, $operation);
            } elseif ($operation instanceof UnaryFilterOperation) {
                $this->applyUnaryFilter($exposedField->getFieldName(), $operation);
            }
        }

        if (count($this->expressions) > 0) {
            $this->queryBuilder->andWhere(...$this->expressions);
        }
    }

    /**
     * @throws InvalidFilterOperatorException
     */
    private function getOperation(FilterAction $action, ExposedField $exposedField): AbstractFilterOperation
    {
        $operator = $action->getOperator();

        if (!(isset($this->ops[$operator]) && in_array($operator, $exposedField->getOperators()))) {
            // If the exposed field references a filter that isn't registered that should not be shown here.
            $sharedFields = array_intersect(array_keys($this->ops), $exposedField->getOperators());
            $supportedFields = implode(', ', $sharedFields);

            $message = sprintf(
                'Unknown operator "%s". Supported values for field %s are: [%s]',
                $operator,
                $action->getPublicFieldName(),
                $supportedFields
            );

            throw new InvalidFilterOperatorException($message);
        }

        return $this->ops[$operator];
    }

    private function applyBinaryFilter(
        ExposedField $field,
        FilterAction $action,
        BinaryFilterOperation $operation
    ): void {
        $paramName = $this->getNextParameterName($field->getFieldName(), $action->getOperator());

        $aliasedFieldName = sprintf(
            '%s.%s',
            $this->queryBuilderMetadata->getEntityToAliasMap()[$field->getClassName()],
            $field->getFieldName()
        );

        $this->expressions[] = $operation->getOperationResult($aliasedFieldName, ":$paramName");
        $this->queryBuilder->setParameter($paramName, $operation->getValue($action->value));
    }

    private function getNextParameterName(string $field, string $operator): string
    {
        $paramName = "doctrine_filter_{$field}_{$operator}_$this->parameterIndex";
        ++$this->parameterIndex;

        return $paramName;
    }

    private function applyUnaryFilter(string $field, UnaryFilterOperation $operation): void
    {
        $this->expressions[] = $operation->getOperationResult(sprintf(
            '%s.%s',
            $this->queryBuilderMetadata->getRootAlias(),
            $field
        ));
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    private function getExposedFieldForAction(FilterAction $action): ?ExposedField
    {
        $alias = $action->getEntityAlias() ?: $this->queryBuilderMetadata->getRootAlias();
        $entity = $this->queryBuilderMetadata->getAliasToEntityMap()[$alias] ?? null;

        if (!$entity) {
            return null;
        }

        return $this->exposedFields[$entity][$action->getPublicFieldName()] ?? null;
    }
}
