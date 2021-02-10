<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use PHPStan\Testing\TestCase;

abstract class BaseTestCase extends TestCase
{
    /** @var EntityManager  */
    protected $entityManager;

    protected function setUp(): void
    {
        $config = Setup::createConfiguration(true);
        $driver = new AnnotationDriver(new AnnotationReader(), [
            __DIR__ . '/Entity'
        ]);

        $config->setMetadataDriverImpl($driver);
        $this->entityManager = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = new QueryBuilder($this->entityManager);
        $qb->from(TestEntity::class, 'x')->select('x');

        return $qb;
    }

}