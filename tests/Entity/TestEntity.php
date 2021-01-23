<?php

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TestEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    public $name;

    /**
     * @ORM\Column(name="age", type="int")
     */
    public $age;

    /**
     * @ORM\Column(type="json")
     *
     * @var array<string>
     */
    public $tag = [];
}