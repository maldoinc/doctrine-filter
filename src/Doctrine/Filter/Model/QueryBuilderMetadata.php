<?php

namespace Maldoinc\Doctrine\Filter\Model;

class QueryBuilderMetadata
{
    private ?string $rootAlias;

    /** @var array<string, class-string> */
    private array $aliasToEntityMap;

    /** @var array<class-string, string> */
    private array $entityToAliasMap;

    /**
     * @param array<string, class-string> $aliasToEntityMap
     */
    public function __construct(?string $rootAlias, array $aliasToEntityMap)
    {
        $this->rootAlias = $rootAlias;
        $this->aliasToEntityMap = $aliasToEntityMap;
        $this->entityToAliasMap = array_flip($aliasToEntityMap);
    }

    public function getRootAlias(): ?string
    {
        return $this->rootAlias;
    }

    /**
     * @return array<string, class-string>
     */
    public function getAliasToEntityMap(): array
    {
        return $this->aliasToEntityMap;
    }

    /**
     * @return array<class-string, string>
     */
    public function getEntityToAliasMap(): array
    {
        return $this->entityToAliasMap;
    }
}
