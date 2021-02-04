<?php

namespace Maldoinc\Doctrine\Filter\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Expose
{
    public $serializedName = null;
}