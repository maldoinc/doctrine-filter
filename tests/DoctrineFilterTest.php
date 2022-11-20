<?php

namespace App\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use Maldoinc\Doctrine\Filter\ExposedFieldsReader;
use Maldoinc\Doctrine\Filter\Extension\PresetFilters;

class DoctrineFilterTest extends BaseTestCase
{
    private function createFilter(QueryBuilder $queryBuilder): DoctrineFilter
    {
        return new DoctrineFilter(
            $queryBuilder,
            (new ExposedFieldsReader(new AnnotationReader()))->readExposedFields($queryBuilder),
            [new PresetFilters()]
        );
    }

    private function isValidDql(QueryBuilder $queryBuilder): bool
    {
        $parser = new Parser($queryBuilder->getQuery());
        $parser->parse();

        return true;
    }

    public function applyFromQueryStringDataProvider(): array
    {
        return [
            [
                'x.age > :doctrine_filter_age_gt_0',
                'age[gt]=18',
                ['doctrine_filter_age_gt_0' => 18],
            ],

            [
                'x.age > :doctrine_filter_age_gt_0 AND x.age < :doctrine_filter_age_lt_1',
                'age[gt]=18&age[lt]=100',
                ['doctrine_filter_age_gt_0' => 18, 'doctrine_filter_age_lt_1' => 100],
            ],

            [
                'x.tag IN(:doctrine_filter_tag_in_0)',
                'tag[in][]=red&tag[in][]=green&tag[in][]=blue',
                ['doctrine_filter_tag_in_0' => ['red', 'green', 'blue']],
            ],

            [
                'x.tag NOT IN(:doctrine_filter_tag_not_in_0)',
                'tag[not_in][]=red&tag[not_in][]=green&tag[not_in][]=blue',
                ['doctrine_filter_tag_not_in_0' => ['red', 'green', 'blue']],
            ],

            [
                'x.id IS NULL',
                'id[is_null]',
                [],
            ],

            [
                'x.id IS NOT NULL',
                'id[is_not_null]',
                [],
            ],

            [
                'x.name = :doctrine_filter_name_eq_0',
                'name[eq]=Jimothy',
                ['doctrine_filter_name_eq_0' => 'Jimothy'],
            ],

            [
                'x.name <> :doctrine_filter_name_neq_0',
                'name[neq]=Jimothy',
                ['doctrine_filter_name_neq_0' => 'Jimothy'],
            ],

            [
                'x.name LIKE :doctrine_filter_name_contains_0',
                'name[contains]=a',
                ['doctrine_filter_name_contains_0' => '%a%'],
            ],

            [
                'x.name LIKE :doctrine_filter_name_ends_with_0',
                'name[ends_with]=a',
                ['doctrine_filter_name_ends_with_0' => '%a'],
            ],

            [
                'x.name LIKE :doctrine_filter_name_starts_with_0',
                'name[starts_with]=a',
                ['doctrine_filter_name_starts_with_0' => 'a%'],
            ],

            [
                'x.name LIKE :doctrine_filter_name_starts_with_0',
                'name[starts_with]=%',
                ['doctrine_filter_name_starts_with_0' => '\\%%'],
            ],

            [
                'x.serializedWithUnderscores = :doctrine_filter_serializedWithUnderscores_eq_0',
                'serialized_with_underscores[eq]=1',
                ['doctrine_filter_serializedWithUnderscores_eq_0' => 1],
            ],

            [
                'x.age >= :doctrine_filter_age_gte_0',
                'age[gte]=1',
                ['doctrine_filter_age_gte_0' => 1],
            ],

            [
                'x.age <= :doctrine_filter_age_lte_0',
                'age[lte]=1',
                ['doctrine_filter_age_lte_0' => 1],
            ],
        ];
    }

    public function testNotExposedOrUknownFields()
    {
        $qb = $this->createQueryBuilder();
        $filter = $this->createFilter($qb);

        $filter->apply(ActionList::fromQueryString('not_exposed[gte]=5&unknown_field[yes]=no&hello=world'));
        $this->assertEquals("SELECT x FROM App\Tests\Entity\TestEntity x", $qb->getQuery()->getDQL());
    }

    /**
     * @dataProvider applyFromQueryStringDataProvider
     */
    public function testApplyFromQueryStringKnownFields($filterQuery, $queryString, $parameters)
    {
        $qb = $this->createQueryBuilder();

        $this->createFilter($qb)->apply(ActionList::fromQueryString($queryString));
        $baseQuery = "SELECT x FROM App\Tests\Entity\TestEntity x WHERE ";

        $this->assertEquals($baseQuery.$filterQuery, $qb->getQuery()->getDQL());

        /** @var Parameter $parameter */
        foreach ($qb->getParameters() as $parameter) {
            $this->assertArrayHasKey($parameter->getName(), $parameters);
            $this->assertEquals($parameters[$parameter->getName()], $parameter->getValue());
        }

        $this->assertTrue($this->isValidDql($qb));
    }

    public function testNoRootAlias()
    {
        $this->expectException(EmptyQueryBuilderException::class);

        (new DoctrineFilter(new QueryBuilder($this->entityManager), [], [new PresetFilters()]))->apply(new ActionList([], []));
    }

    public function testFromQueryStringIgnoreKeyValueFormat()
    {
        $qb = $this->createQueryBuilder();
        $this->createFilter($qb)->apply(ActionList::fromQueryString('ignored=this&age[gt]=50&this=that'));

        $this->assertEquals(
            'SELECT x FROM App\Tests\Entity\TestEntity x WHERE x.age > :doctrine_filter_age_gt_0',
            $qb->getQuery()->getDQL()
        );
        $this->assertEquals(1, $qb->getParameters()->count());
        $this->assertEquals('doctrine_filter_age_gt_0', $qb->getParameters()->first()->getName());
        $this->assertEquals(50, $qb->getParameters()->first()->getValue());
        $this->assertTrue($this->isValidDql($qb));
    }

    public function testInvalidOperators()
    {
        $this->expectException(InvalidFilterOperatorException::class);
        $qb = $this->createQueryBuilder();

        $this->createFilter($qb)->apply(ActionList::fromQueryString(
            'ignored=this&age[gt]=50&age[DUMMY]=yes'
        ));
    }

    public function orderByDataProvider(): array
    {
        return [
            ['', ''],
            ['x.id asc, x.name desc', 'orderBy[id]=asc&orderBy[name]=desc'],
            ['x.id desc', 'orderBy[id]=asc&orderBy[id]=desc'],
            ['x.id desc', 'orderBy[id]=asc&orderBy[id]=desc'],
            ['', 'this=that&orderBy=asc']
        ];
    }

    /**
     * @dataProvider orderByDataProvider
     */
    public function testOrderBy($orderByClause, $queryString)
    {
        $qb = $this->createQueryBuilder();
        $this->createFilter($qb)->apply(ActionList::fromQueryString($queryString, 'orderBy'));

        $dql = $orderByClause
            ? "SELECT x FROM App\Tests\Entity\TestEntity x ORDER BY $orderByClause"
            : "SELECT x FROM App\Tests\Entity\TestEntity x";

        $this->assertEquals($dql, $qb->getQuery()->getDQL());
        $this->isValidDql($qb);
    }
}
