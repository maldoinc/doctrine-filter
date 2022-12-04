<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Maldoinc\Doctrine\Filter\Model\ExposedField;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\AttributeReaderInterface;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\DoctrineAnnotationReader;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\NativeAttributeReader;
use Maldoinc\Doctrine\Filter\Reader\ExposedFieldsReader;

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
        $this->assertEquals(
            [TestEntity::class => [
                'id' => new ExposedField(TestEntity::class, 'id', PresetFilterProvider::ALL_PRESETS),
                'name' => new ExposedField(TestEntity::class, 'name', PresetFilterProvider::ALL_PRESETS),
                'age' => new ExposedField(TestEntity::class, 'age', PresetFilterProvider::ALL_PRESETS),
                'tag' => new ExposedField(TestEntity::class, 'tag', PresetFilterProvider::ALL_PRESETS),
                'serialized_with_underscores' => new ExposedField(
                    TestEntity::class,
                    'serializedWithUnderscores',
                    [PresetFilterProvider::EQ, PresetFilterProvider::NEQ]
                ),
                'dummyField' => new ExposedField(TestEntity::class, 'dummyField', ['is_dummy']),
            ]],
            (new ExposedFieldsReader($reader))->getExposedFields([TestEntity::class])
        );
    }
}
