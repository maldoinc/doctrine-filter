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
     * @FilterExpose
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @FilterExpose
     */
    public $name;

    /**
     * @ORM\Column(name="age", type="int")
     * @FilterExpose
     */
    public $age;

    /**
     * @ORM\Column(type="json")
     * @FilterExpose
     *
     * @var array<string>
     */
    public $tag = [];

    public $notMappedForFiltering;

    /**
     * @ORM\Column(type="integer")
     * @FilterExpose(serializedName="serialized_with_underscores")
     */
    public $serializedWithUnderscores;
}