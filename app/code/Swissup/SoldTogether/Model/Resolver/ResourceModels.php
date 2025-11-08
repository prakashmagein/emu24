<?php

namespace Swissup\SoldTogether\Model\Resolver;

class ResourceModels
{
    private $resourceModel;

    public function __construct(
        array $resourceModel = []
    ) {
        $this->resourceModel = $resourceModel;
    }

    public function get(string $linkType)
    {
        return $this->resourceModel[$linkType] ?: null;
    }
}
