<?php

declare(strict_types=1);

namespace Swissup\Easybanner\Model\Config\ContentType\AdditionalData;

interface ProviderInterface
{
    /**
     * Get data from the provider
     * @param string $itemName - the name of the item to use as key in returned array
     * @return array
     */
    public function getData(string $itemName): array;
}
