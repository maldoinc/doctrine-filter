<?php

namespace App\Tests;

use App\Tests\Entity\Author;
use App\Tests\Entity\Book;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\DoctrineAnnotationReader;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\NativeAttributeReader;
use Maldoinc\Doctrine\Filter\Reader\ExposedFieldsReader;

class DoctrineFilterTest extends BaseTestCase
{
    /**
     * @return \Generator<DoctrineFilter>
     */
    private function getFilters(QueryBuilder $qb = null): \Generator
    {
        $qb = $qb ?: $this->createQueryBuilder();
        yield new DoctrineFilter(
            $qb,
            new ExposedFieldsReader(new DoctrineAnnotationReader(new AnnotationReader())),
            [new PresetFilterProvider()]
        );

        if (PHP_MAJOR_VERSION >= 8) {
            $qb8 = $this->createQueryBuilder();

            yield new DoctrineFilter(
                $qb8,
                new ExposedFieldsReader(new NativeAttributeReader()),
                [new PresetFilterProvider()]
            );
        }
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

    public function testNotExposedOrUnknownFields()
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply(ActionList::fromQueryString('not_exposed[gte]=5&unknown_field[yes]=no&hello=world'));
            $this->assertEquals("SELECT x FROM App\Tests\Entity\TestEntity x", $filter->getQueryBuilder()->getQuery()->getDQL());
        }
    }

    /**
     * @dataProvider applyFromQueryStringDataProvider
     */
    public function testApplyFromQueryStringKnownFields($filterQuery, $queryString, $parameters)
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply(ActionList::fromQueryString($queryString));
            $baseQuery = "SELECT x FROM App\Tests\Entity\TestEntity x WHERE ";

            $this->assertEquals($baseQuery . $filterQuery, $filter->getQueryBuilder()->getQuery()->getDQL());

            /** @var Parameter $parameter */
            foreach ($filter->getQueryBuilder()->getParameters() as $parameter) {
                $this->assertArrayHasKey($parameter->getName(), $parameters);
                $this->assertEquals($parameters[$parameter->getName()], $parameter->getValue());
            }

            $this->assertTrue($this->isValidDql($filter->getQueryBuilder()));
        }
    }

    public function testSimpleEquality()
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply(ActionList::fromQueryString(
                'id=5&name=hello&page=3&foo=bar&ignored[maybe]=yes',
                'orderBy',
                true
            ));

            $this->assertEquals(
                'SELECT x FROM App\Tests\Entity\TestEntity x WHERE ' .
                'x.id = :doctrine_filter_id_eq_0 AND x.name = :doctrine_filter_name_eq_1',
                $filter->getQueryBuilder()->getDQL()
            );
        }
    }

    public function testNoRootAlias()
    {
        $this->expectException(EmptyQueryBuilderException::class);

        $exposedFieldReader = new ExposedFieldsReader(new DoctrineAnnotationReader(new AnnotationReader()));
        $doctrineFilter = new DoctrineFilter(
            new QueryBuilder($this->entityManager),
            $exposedFieldReader,
            [new PresetFilterProvider()]
        );

        $doctrineFilter->apply(new ActionList([], []));
    }

    public function testFromQueryStringIgnoreKeyValueFormat()
    {
        foreach ($this->getFilters() as $filter) {
            $qb = $filter->getQueryBuilder();
            $filter->apply(ActionList::fromQueryString('ignored=this&age[gt]=50&this=that'));

            $this->assertEquals(
                'SELECT x FROM App\Tests\Entity\TestEntity x WHERE x.age > :doctrine_filter_age_gt_0',
                $qb->getQuery()->getDQL()
            );
            $this->assertEquals(1, $qb->getParameters()->count());
            $this->assertEquals('doctrine_filter_age_gt_0', $qb->getParameters()->first()->getName());
            $this->assertEquals(50, $qb->getParameters()->first()->getValue());
            $this->assertTrue($this->isValidDql($qb));
        }
    }

    public function testUnknownOperators()
    {
        $this->expectException(InvalidFilterOperatorException::class);
        $this->expectExceptionMessage('Unknown operator "DUMMY". Supported values for field age are: [');

        foreach ($this->getFilters() as $filter) {
            $filter->apply(ActionList::fromQueryString('ignored=this&age[gt]=50&age[DUMMY]=yes'));
        }
    }

    public function testKnownOperatorOnUnsupportedField()
    {
        $this->expectException(InvalidFilterOperatorException::class);
        $this->expectExceptionMessage(
            'Unknown operator "gt". Supported values for field serialized_with_underscores are: [eq, neq]'
        );

        foreach ($this->getFilters() as $filter) {
            $filter->apply(ActionList::fromQueryString('serialized_with_underscores[gt]=5'));
        }
    }

    public function orderByDataProvider(): array
    {
        return [
            ['', ''],
            ['x.id asc, x.name desc', 'orderBy[id]=asc&orderBy[name]=desc'],
            ['x.id desc', 'orderBy[id]=asc&orderBy[id]=desc'],
            ['x.id desc', 'orderBy[id]=asc&orderBy[id]=desc'],
            ['', 'this=that&orderBy=asc'],
        ];
    }

    /**
     * @dataProvider orderByDataProvider
     */
    public function testOrderBy($orderByClause, $queryString)
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply(ActionList::fromQueryString($queryString, 'orderBy'));

            $dql = $orderByClause
                ? "SELECT x FROM App\Tests\Entity\TestEntity x ORDER BY $orderByClause"
                : "SELECT x FROM App\Tests\Entity\TestEntity x";

            $this->assertEquals($dql, $filter->getQueryBuilder()->getQuery()->getDQL());
            $this->isValidDql($filter->getQueryBuilder());
        }
    }

    public function testDoesNotRemoveExistingFilters()
    {
        $qb = $this->createQueryBuilder();
        $qb->orWhere($qb->expr()->isNotNull('x.id'), $qb->expr()->isNull('x.deletedAt'));
        $qb->setParameter(0, 1);

        $reader = new ExposedFieldsReader(new DoctrineAnnotationReader(new AnnotationReader()));
        $doctrineFilter = new DoctrineFilter($qb, $reader, [new PresetFilterProvider()]);
        $doctrineFilter->apply(ActionList::fromQueryString('name[eq]=Test'));

        $this->assertEquals(
            'SELECT x FROM App\Tests\Entity\TestEntity x WHERE (x.id IS NOT NULL OR x.deletedAt IS NULL) ' .
            'AND x.name = :doctrine_filter_name_eq_0',
            $qb->getQuery()->getDQL()
        );
    }

    public function testFilterJoinedEntities()
    {
        $qb = new QueryBuilder($this->entityManager);
        $qb->from(Book::class, 'b')->select('b')->join(Author::class, 'author');

        foreach ($this->getFilters($qb) as $filter) {
            $filter->apply(ActionList::fromQueryString('author.id[eq]=5&name=GoodBook', null, true));

            $this->assertEquals('SELECT b FROM App\Tests\Entity\Book b ' .
                'INNER JOIN App\Tests\Entity\Author author WHERE author.id = :doctrine_filter_id_eq_0 AND ' .
                'b.name = :doctrine_filter_name_eq_1', $qb->getDQL());

            $this->assertEquals(new ArrayCollection([
                new Parameter('doctrine_filter_id_eq_0', '5'),
                new Parameter('doctrine_filter_name_eq_1', 'GoodBook'),
            ]), $qb->getParameters());
        }

        return $qb;
    }

    public function testFilterJoinedEntitiesAliasedName()
    {
        $qb = new QueryBuilder($this->entityManager);
        $qb->from(Book::class, 'b')->select('b')->join(Author::class, 'author_secondary');

        foreach ($this->getFilters($qb) as $filter) {
            // Also check that simple equality works for joined entities.
            $filter->apply(ActionList::fromQueryString('author_secondary.name=Ben', null, true));

            $this->assertEquals(
                'SELECT b FROM App\Tests\Entity\Book b INNER JOIN ' .
                'App\Tests\Entity\Author author_secondary WHERE author_secondary.name = :doctrine_filter_name_eq_0',
                $qb->getDQL()
            );

            $this->assertEquals(
                new ArrayCollection([new Parameter('doctrine_filter_name_eq_0', 'Ben')]),
                $qb->getParameters()
            );
        }

        return $qb;
    }
}
