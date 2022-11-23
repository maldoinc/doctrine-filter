<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Maldoinc\Doctrine\Filter\ExposedField;
use Maldoinc\Doctrine\Filter\ExposedFieldsReader;
use Maldoinc\Doctrine\Filter\Extension\PresetFilters;
use Maldoinc\Doctrine\Filter\Reader\AttributeReaderInterface;
use Maldoinc\Doctrine\Filter\Reader\DoctrineAnnotationReader;
use Maldoinc\Doctrine\Filter\Reader\NativeAttributeReader;

class ExposedFieldsReaderTest extends BaseTestCase
{
    public function readerDataProvider(): \Generator
    {
        yield [new DoctrineAnnotationReader(new AnnotationReader())];

        if (PHP_MAJOR_VERSION >= 8) {
            yield [new NativeAttributeReader()];
        }
    }

    /**
     * @dataProvider readerDataProvider
     */
    public function testReader(AttributeReaderInterface $reader)
    {
        $qb = $this->createQueryBuilder();
        $this->assertEquals(
            [TestEntity::class => [
                'id' => new ExposedField('id', PresetFilters::ALL_PRESETS),
                'name' => new ExposedField('name', PresetFilters::ALL_PRESETS),
                'age' => new ExposedField('age', PresetFilters::ALL_PRESETS),
                'tag' => new ExposedField('tag', PresetFilters::ALL_PRESETS),
                'serialized_with_underscores' => new ExposedField('serializedWithUnderscores', PresetFilters::ALL_PRESETS),
            ]],
            (new ExposedFieldsReader($reader))->readExposedFields($qb)
        );
    }
}
