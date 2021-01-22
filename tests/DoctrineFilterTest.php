<?php

namespace App\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Exception\InvalidFilterOperatorException;
use PHPUnit\Framework\TestCase;

class DoctrineFilterTest extends TestCase
{
    private function createQueryBuilder(): QueryBuilder
    {
        $dbParams = array('driver' => 'pdo_sqlite', 'memory' => true);
        $entityManager = EntityManager::create($dbParams, Setup::createAnnotationMetadataConfiguration([]));

        $qb = new QueryBuilder($entityManager);
        $qb->from('Entity', 'x');

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
                'x.tag IN :doctrine_filter_tag_in_0',
                ['tag' => ['in' => ['red', 'green', 'blue']]],
                ['doctrine_filter_tag_in_0' => ['red', 'green', 'blue']]
            ],

            [
                'x.field IS NULL',
                ['field' => ['IS_NULL' => 1]],
                []
            ],

            [
                'x.field IS NOT NULL',
                ['field' => ['IS_NOT_NULL' => 1]],
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
        ];
    }


    /**
     * @dataProvider applyFromArrayDataProvider
     */
    public function testApplyFromArray($filterQuery, $array, $parameters)
    {
        $qb = $this->createQueryBuilder();

        DoctrineFilter::applyFromArray($qb, $array);
        $baseQuery = "SELECT FROM Entity x";

        if (count(array_keys($array)) > 0) {
            $baseQuery .= " WHERE ";
        }

        $this->assertEquals($baseQuery . $filterQuery, $qb->getQuery()->getDQL());

        /** @var Parameter $parameter */
        foreach ($qb->getParameters() as $parameter) {
            $this->assertArrayHasKey($parameter->getName(), $parameters);
            $this->assertEquals($parameters[$parameter->getName()], $parameter->getValue());
        }
    }

    public function testFromQueryStringIgnoreKeyValueFormat()
    {
        $qb = $this->createQueryBuilder();
        DoctrineFilter::applyFromQueryString($qb, 'ignored=this&age[gt]=50&this=that');

        $this->assertEquals(
            'SELECT FROM Entity x WHERE x.age > :doctrine_filter_age_gt_0',
            $qb->getQuery()->getDQL()
        );
        $this->assertEquals(1, $qb->getParameters()->count());
        $this->assertEquals('doctrine_filter_age_gt_0', $qb->getParameters()->first()->getName());
        $this->assertEquals(50, $qb->getParameters()->first()->getValue());
    }

    public function testInvalidOperators()
    {
        $this->expectException(InvalidFilterOperatorException::class);
        DoctrineFilter::applyFromQueryString(
            $this->createQueryBuilder(),
            'ignored=this&age[gt]=50&prop[DUMMY]=yes'
        );
    }
}