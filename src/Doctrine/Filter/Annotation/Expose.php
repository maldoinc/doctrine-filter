<?php

namespace Maldoinc\Doctrine\Filter\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Expose
{
    public ?string $serializedName = null;

    /** @var string[] */
    public array $operators = [];
}
