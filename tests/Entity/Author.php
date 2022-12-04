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
class Author
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
}
