<?php

namespace Maldoinc\Doctrine\Filter\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Expose
{
    /** @var string */
    public $serializedName = null;
}
