<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use App\Tests\Entity\User;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use Maldoinc\Doctrine\Filter\Exception\EmptyQueryBuilderException;
use PHPUnit\Framework\TestCase;

class DoctrineFilterTest extends TestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $config = Setup::createConfiguration(true);
        $driver = new AnnotationDriver(new AnnotationReader(), [
            __DIR__ . '/Entity'
        ]);

        $config->setMetadataDriverImpl($driver);
        $this->entityManager = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
    }

    private function isValidDql(QueryBuilder $queryBuilder): bool
    {
        $parser = new Parser($queryBuilder->getQuery());
        $parser->parse();

        return true;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $qb = new QueryBuilder($this->entityManager);
        $qb->from(TestEntity::class, 'x')->select('x');

        return $qb;
    }

    public function applyFromArrayDataProvider(): array
    {
        return [
            ['', [], []],

            [
                'x.age > :doctrine_filter_age_gt_0',
                ['age' => ['gt' => 18]],
                ['doctrine_filter_age_gt_0' => 18]
            ],

            [
                'x.age > :doctrine_filter_age_gt_0 AND x.age < :doctrine_filter_age_lt_1',
                ['age' => ['gt' => 18, 'lt' => 100]],
                ['doctrine_filter_age_gt_0' => 18, 'doctrine_filter_age_lt_1' => 100]
            ],

            [
                'x.tag IN(:doctrine_filter_tag_in_0)',
                ['tag' => ['in' => ['red', 'green', 'blue']]],
                ['doctrine_filter_tag_in_0' => ['red', 'green', 'blue']]
            ],

            [
                'x.tag NOT IN(:doctrine_filter_tag_not_in_0)',
                ['tag' => ['not_in' => ['red', 'green', 'blue']]],
                ['doctrine_filter_tag_not_in_0' => ['red', 'green', 'blue']]
            ],

            [
                'x.id IS NULL',
                ['id' => ['IS_NULL' => 1]],
                []
            ],

            [
                'x.id IS NOT NULL',
                ['id' => ['IS_NOT_NULL' => 1]],
                []
            ],

            [
                'x.name = :doctrine_filter_name_eq_0',
                ['name' => ['eq' => 'Jimothy']],
                ['doctrine_filter_name_eq_0' => 'Jimothy']
            ],

            [
                'x.name <> :doctrine_filter_name_neq_0',
                ['name' => ['neq' => 'Jimothy']],
                ['doctrine_filter_name_neq_0' => 'Jimothy']
            ],

            [
                "x.name LIKE :doctrine_filter_name_contains_0",
                ['name' => ['contains' => 'a']],
                ['doctrine_filter_name_contains_0' => '%a%']
            ],

            [
                "x.name LIKE :doctrine_filter_name_ends_with_0",
                ['name' => ['ends_with' => 'a']],
                ['doctrine_filter_name_ends_with_0' => '%a']
            ],

            [
                "x.name LIKE :doctrine_filter_name_starts_with_0",
                ['name' => ['starts_with' => 'a']],
                ['doctrine_filter_name_starts_with_0' => 'a%']
            ],

            [
                "x.name LIKE :doctrine_filter_name_starts_with_0",
                ['name' => ['starts_with' => '%']],
                ['doctrine_filter_name_starts_with_0' => '\\%%']
            ],

            [
                "x.serializedWithUnderscores = :doctrine_filter_serializedWithUnderscores_eq_0",
                ["serialized_with_underscores" => ["eq" => 1]],
                ['doctrine_filter_serializedWithUnderscores_eq_0' => 1]
            ],

            [
                "x.age >= :doctrine_filter_age_gte_0",
                ["age" => ["gte" => 1]],
                ['doctrine_filter_age_gte_0' => 1]
            ],

            [
                "x.age <= :doctrine_filter_age_lte_0",
                ["age" => ["lte" => 1]],
                ['doctrine_filter_age_lte_0' => 1]
            ],
        ];
    }

    public function testNotExposedFields()
    {
        $qb = $this->createQueryBuilder();
        $filter = new DoctrineFilter($qb);

        $filter->applyFromArray(['not_exposed' => ['gte' => 5]]);
        $this->assertEquals("SELECT x FROM App\Tests\Entity\TestEntity x", $qb->getQuery()->getDQL());
    }


    /**
     * @dataProvider applyFromArrayDataProvider
     */
    public function testApplyFromArray($filterQuery, $array, $parameters)
    {
        $qb = $this->createQueryBuilder();

        (new DoctrineFilter($qb))->applyFromArray($array);
        $baseQuery = "SELECT x FROM App\Tests\Entity\TestEntity x";

        if (count(array_keys($array)) > 0) {
            $baseQuery .= " WHERE ";
        }

        $this->assertEquals($baseQuery . $filterQuery, $qb->getQuery()->getDQL());

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

        (new DoctrineFilter(new QueryBuilder($this->entityManager)))->applyFromArray([]);
    }

    public function testFromQueryStringIgnoreKeyValueFormat()
    {
        $qb = $this->createQueryBuilder();
        (new DoctrineFilter($qb))->applyFromQueryString('ignored=this&age[gt]=50&this=that');

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

        (new DoctrineFilter($qb))->applyFromQueryString('ignored=this&age[gt]=50&age[DUMMY]=yes');
    }

    public function orderByDataProvider(): array
    {
        return [
            ['', ''],
            ['x.id asc, x.name desc', 'orderBy[id]=asc&orderBy[name]=desc'],
            ['x.id desc', 'orderBy[id]=asc&orderBy[id]=desc'],
            ['x.id desc', 'orderBy[id]=asc&orderBy[id]=desc'],
        ];
    }

    /**
     * @dataProvider orderByDataProvider
     */
    public function testOrderBy($orderByClause, $queryString)
    {
        $qb = $this->createQueryBuilder();
        (new DoctrineFilter($qb))->applyFromQueryString($queryString);

        $dql = $queryString
            ? "SELECT x FROM App\Tests\Entity\TestEntity x ORDER BY $orderByClause"
            : "SELECT x FROM App\Tests\Entity\TestEntity x";

        $this->assertEquals($dql, $qb->getQuery()->getDQL());
        $this->isValidDql($qb);
    }
}