<?php

namespace App\Tests;

use App\Tests\Entity\TestEntity;
use Maldoinc\Doctrine\Filter\ExposedFieldsReader;
use PHPUnit\Framework\TestCase;

class ExposedFieldsReaderTest extends TestCase
{
    public function readerDataProvider()
    {
        return [
            [TestEntity::class, [
                'id' => 'id',
                'name' => 'name',
                'age' => 'age',
                'tag' => 'tag',
                'serialized_with_underscores' => 'serializedWithUnderscores'
            ]]
        ];
    }

    /**
     * @dataProvider readerDataProvider
     */
    public function testReader($class, $fields)
    {
        $this->assertEquals($fields, ExposedFieldsReader::readExposedFields($class));
    }
}