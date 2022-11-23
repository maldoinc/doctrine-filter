<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpLanguageLevelInspection
 */

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Maldoinc\Doctrine\Filter\Annotation\Expose;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;
use Maldoinc\Doctrine\Filter\Extension\PresetFilters;

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
    #[Expose(operators: PresetFilters::ALL_PRESETS)]
    public int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     */
    #[Expose(operators: PresetFilters::ALL_PRESETS)]
    public string $name;

    /**
     * @ORM\Column(name="age", type="int")
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     */
    #[Expose(operators: PresetFilters::ALL_PRESETS)]
    public int $age;

    /**
     * @ORM\Column(type="json")
     * @FilterExpose(operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS)
     *
     * @var array<string>
     */
    #[Expose(operators: PresetFilters::ALL_PRESETS)]
    public array $tag = [];

    public int $notMappedForFiltering;

    /**
     * @ORM\Column
     * @FilterExpose(operators={"is_dummy"})
     */
    #[FilterExpose(operators: ["is_dummy"])]
    public int $dummyField;

    /**
     * @ORM\Column(type="integer")
     * @FilterExpose(
     *     operators=Maldoinc\Doctrine\Filter\Extension\PresetFilters::ALL_PRESETS,
     *     serializedName="serialized_with_underscores"
     * )
     */
    #[Expose(serializedName: "serialized_with_underscores", operators: PresetFilters::ALL_PRESETS)]
    public int $serializedWithUnderscores;
}
