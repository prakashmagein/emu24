<?php

declare(strict_types=1);

namespace Swissup\Ajaxsearch\Model\Resolver\CategoryOptions;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved products
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Magento\Catalog\Model\Category::CACHE_TAG;

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
            foreach (['id'] as $idKey) {
                if (isset($item[$idKey])) {
                    $ids[] = sprintf('%s_%s', $this->cacheTag, $item[$idKey]);
                    break;
                }
            }
        }
        if (!empty($ids)) {
            array_unshift($ids, $this->cacheTag);
        }

        return $ids;
    }
}
