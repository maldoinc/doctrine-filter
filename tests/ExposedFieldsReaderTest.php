<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Maldoinc\Doctrine\Filter\ExposedFieldsReader;

class ExposedFieldsReaderTest extends BaseTestCase
{
    public function testReader()
    {
        $this->assertEquals(
            [TestEntity::class => [
                'id' => 'id',
                'name' => 'name',
                'age' => 'age',
                'tag' => 'tag',
                'serialized_with_underscores' => 'serializedWithUnderscores'
            ]],
            (new ExposedFieldsReader(new AnnotationReader()))->readExposedFields($this->createQueryBuilder())
        );
    }
}