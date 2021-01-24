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
use PHPUnit\Framework\TestCase;

class DoctrineFilterTest extends TestCase
{
    private function isValidDql(QueryBuilder $queryBuilder): bool
    {
        $parser = new Parser($queryBuilder->getQuery());
        $parser->parse();

        return true;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $config = Setup::createConfiguration(true);
        $driver = new AnnotationDriver(new AnnotationReader(), [
            __DIR__ . '/Entity'
        ]);

        $config->setMetadataDriverImpl($driver);
        $entityManager = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);

        $qb = new QueryBuilder($entityManager);
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
                'x.tag IN (:doctrine_filter_tag_in_0)',
                ['tag' => ['in' => ['red', 'green', 'blue']]],
                ['doctrine_filter_tag_in_0' => ['red', 'green', 'blue']]
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
                'x.name != :doctrine_filter_name_neq_0',
                ['name' => ['neq' => 'Jimothy']],
                ['doctrine_filter_name_neq_0' => 'Jimothy']
            ],

            [
                "x.name like :doctrine_filter_name_contains_0",
                ['name' => ['contains' => 'a']],
                ['doctrine_filter_name_contains_0' => '%a%']
            ],

            [
                "x.name like :doctrine_filter_name_ends_with_0",
                ['name' => ['ends_with' => 'a']],
                ['doctrine_filter_name_ends_with_0' => '%a']
            ],

            [
                "x.name like :doctrine_filter_name_starts_with_0",
                ['name' => ['starts_with' => 'a']],
                ['doctrine_filter_name_starts_with_0' => 'a%']
            ],

            [
                "x.name like :doctrine_filter_name_starts_with_0",
                ['name' => ['starts_with' => '%']],
                ['doctrine_filter_name_starts_with_0' => '\\%%']
            ],
        ];
    }


    /**
     * @dataProvider applyFromArrayDataProvider
     */
    public function testApplyFromArray($filterQuery, $array, $parameters)
    {
        $qb = $this->createQueryBuilder();

        DoctrineFilter::applyFromArray($qb, $array);
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

    public function testFromQueryStringIgnoreKeyValueFormat()
    {
        $qb = $this->createQueryBuilder();
        DoctrineFilter::applyFromQueryString($qb, 'ignored=this&age[gt]=50&this=that');

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
        DoctrineFilter::applyFromQueryString(
            $this->createQueryBuilder(),
            'ignored=this&age[gt]=50&prop[DUMMY]=yes'
        );
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
        DoctrineFilter::applyFromQueryString($qb, $queryString);

        $dql = $queryString ? "SELECT x FROM App\Tests\Entity\TestEntity x ORDER BY $orderByClause" : "SELECT x FROM App\Tests\Entity\TestEntity x";

        $this->assertEquals($dql, $qb->getQuery()->getDQL());
        $this->isValidDql($qb);
    }
}