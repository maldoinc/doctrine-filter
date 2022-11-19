<?php

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;

/**
 * @ORM\Entity
 */
class TestEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     */
    public $name;

    /**
     * @ORM\Column(name="age", type="int")
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     */
    public $age;

    /**
     * @ORM\Column(type="json")
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     *
     * @var array<string>
     */
    public $tag = [];

    public $notMappedForFiltering;

    /**
     * @ORM\Column(type="integer")
     * @FilterExpose(
     *     operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS,
     *     serializedName="serialized_with_underscores"
     * )
     */
    public $serializedWithUnderscores;
}
