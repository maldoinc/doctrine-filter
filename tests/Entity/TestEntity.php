<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpLanguageLevelInspection
 */

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Maldoinc\Doctrine\Filter\Annotation\Expose;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;

/**
 * @ORM\Entity
 */
class TestEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @FilterExpose(operators=PresetFilterProvider::ALL_PRESETS)
     */
    #[Expose(operators: PresetFilterProvider::ALL_PRESETS)]
    public int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @FilterExpose(operators=PresetFilterProvider::ALL_PRESETS)
     */
    #[Expose(operators: PresetFilterProvider::ALL_PRESETS)]
    public string $name;

    /**
     * @ORM\Column(name="age", type="int")
     * @FilterExpose(operators=PresetFilterProvider::ALL_PRESETS)
     */
    #[Expose(operators: PresetFilterProvider::ALL_PRESETS)]
    public int $age;

    /**
     * @ORM\Column(type="json")
     * @FilterExpose(operators=PresetFilterProvider::ALL_PRESETS)
     *
     * @var array<string>
     */
    #[Expose(operators: PresetFilterProvider::ALL_PRESETS)]
    public array $tag = [];

    public int   $notMappedForFiltering;

    /**
     * @ORM\Column
     * @FilterExpose(operators={"is_dummy"})
     */
    #[FilterExpose(operators: ['is_dummy'])]
    public int $dummyField;

    /**
     * @ORM\Column(type="integer")
     * @FilterExpose(
     *     operators={PresetFilterProvider::EQ, PresetFilterProvider::NEQ},
     *     serializedName="serialized_with_underscores"
     * )
     */
    #[Expose(serializedName: 'serialized_with_underscores', operators: [PresetFilterProvider::EQ, PresetFilterProvider::NEQ])]
    public int $serializedWithUnderscores;
}
