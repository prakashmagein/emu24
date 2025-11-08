<?php
declare(strict_types=1);

namespace Swissup\Pagespeed\Plugin\View\Asset;

use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\GroupedCollection;

/**
 * Plugin to add integrity to assets on page load.
 */
class GroupedCollectionPlugin
{
    private $integrities = [];

    /**
     * Before Plugin to add Properties to JS assets
     *
     * @param GroupedCollection $subject
     * @param AssetInterface $asset
     * @param array $properties
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetFilteredProperties(
        GroupedCollection $subject,
        AssetInterface $asset,
        array $properties = []
    ): array {
        if ($asset instanceof LocalInterface && isset($properties['attributes']['integrity'])) {
            $this->integrities[$asset->getPath()] = $properties['attributes']['integrity'];
            unset($properties['attributes']['integrity']);
            unset($properties['attributes']['crossorigin']);
        }

        return [$asset, $properties];
    }

    public function getIntegrities()
    {
        return $this->integrities;
    }
}
