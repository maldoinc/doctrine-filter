<?php

namespace Maldoinc\Doctrine\Filter\Annotation;

/**
 * @Annotation
 *
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Expose
{
    public ?string $serializedName = null;

    /** @var string[] */
    public array $operators = [];

    /**
     * @param string|array<string, mixed> $serializedName
     * @param string[] $operators
     */
    public function __construct($serializedName = null, array $operators = [])
    {
        // Doctrine annotation data is sent via a single param which contains a map with the arguments
        if (is_array($serializedName)) {
            /* @phpstan-ignore-next-line */
            $this->serializedName = $serializedName['serializedName'] ?? null;

            /* @phpstan-ignore-next-line */
            $this->operators = $serializedName['operators'] ?? [];

            return;
        }

        $this->serializedName = $serializedName;
        $this->operators = $operators;
    }
}
