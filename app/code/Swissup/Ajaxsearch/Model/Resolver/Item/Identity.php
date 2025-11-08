<?php

declare(strict_types=1);

namespace Swissup\Ajaxsearch\Model\Resolver\Item;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved products
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $productCacheTag = \Magento\Catalog\Model\Product::CACHE_TAG;

    /**
     * Get product ids for cache tag
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            if (isset($item['entity_id'])) {
                $ids[] = sprintf('%s_%s', $this->productCacheTag, $item['entity_id']);
            }
        }
        if (!empty($ids)) {
            array_unshift($ids, $this->productCacheTag);
        }

        return $ids;
    }
}
