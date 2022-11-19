<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Maldoinc\Doctrine\Filter\ExposedField;
use Maldoinc\Doctrine\Filter\ExposedFieldsReader;
use Maldoinc\Doctrine\Filter\Extension\PresetFilters;

class ExposedFieldsReaderTest extends BaseTestCase
{
    public function testReader()
    {
        $this->assertEquals(
            [TestEntity::class => [
                'id' => new ExposedField('id', PresetFilters::ALL_PRESETS),
                'name' => new ExposedField('name', PresetFilters::ALL_PRESETS),
                'age' => new ExposedField('age', PresetFilters::ALL_PRESETS),
                'tag' => new ExposedField('tag', PresetFilters::ALL_PRESETS),
                'serialized_with_underscores' => new ExposedField('serializedWithUnderscores', PresetFilters::ALL_PRESETS),
            ]],
            (new ExposedFieldsReader(new AnnotationReader()))->readExposedFields($this->createQueryBuilder())
        );
    }
}
