<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpLanguageLevelInspection
 */

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;

/**
 * @ORM\Entity
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @FilterExpose(operators=PresetFilterProvider::ALL_PRESETS)
     */
    #[FilterExpose(operators: PresetFilterProvider::ALL_PRESETS)]
    public int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @FilterExpose(operators=PresetFilterProvider::ALL_PRESETS)
     */
    #[FilterExpose(operators: PresetFilterProvider::ALL_PRESETS)]
    public string $name;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class)
     *
     * @FilterExpose
     */
    #[ORM\ManyToOne]
    #[FilterExpose]
    public Author $author;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class)
     * @FilterExpose(serializedName="author_serialized_name")
     */
    #[ORM\ManyToOne]
    #[FilterExpose(serializedName: 'author_secondary')]
    public Author $author2;
}
