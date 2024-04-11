<?php

namespace Maldoinc\Doctrine\Filter\QueryBuilder;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Model\QueryBuilderMetadata;

class QueryBuilderMetadataReader
{
    /**
     * @throws EmptyQueryBuilderException
     */
    public static function getMetadata(QueryBuilder $queryBuilder): QueryBuilderMetadata
    {
        if (0 === count($queryBuilder->getRootAliases())) {
            throw new EmptyQueryBuilderException('Query builder must contain at least one alias');
        }

        /** @var array<string, class-string> $aliasToEntityMap */
        $aliasToEntityMap = [$queryBuilder->getRootAliases()[0] => $queryBuilder->getRootEntities()[0]];

        /** @var array<string, array<string, Join>> $joinPart */
        $joinPart = $queryBuilder->getDQLPart('join');

        foreach ($joinPart as $joinMapping) {
            foreach ($joinMapping as $join) {
                /** @var class-string $joinClass */
                $joinClass = $join->getJoin();
                $aliasToEntityMap[$join->getAlias()] = $joinClass;
            }
        }

        return new QueryBuilderMetadata($queryBuilder->getRootAliases()[0] ?? null, $aliasToEntityMap);
    }
}