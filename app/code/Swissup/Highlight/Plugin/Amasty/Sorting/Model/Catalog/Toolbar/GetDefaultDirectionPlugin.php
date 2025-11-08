<?php

namespace Swissup\Highlight\Plugin\Amasty\Sorting\Model\Catalog\Toolbar;

use Amasty\Sorting\Model\Catalog\Toolbar\GetDefaultDirection;

class GetDefaultDirectionPlugin
{
    /**
     * Swissup Highlight specific sorting orders with their default directions
     */
    private const HIGHLIGHT_ORDERS = [
        'featured' => 'asc',//\Swissup\Highlight\Block\ProductList\All::getDefaultSortDirection()
        'news_from_date' => 'desc',//\Swissup\Highlight\Block\ProductList\Attribute\Date::getDefaultSortDirection()
        'news_to_date' => 'desc',
        'special_from_date' => 'desc',
        'special_to_date' => 'desc'
    ];

    /**
     * Override default direction for Swissup Highlight specific orders
     *
     * @param GetDefaultDirection $subject
     * @param string $result
     * @param string $order
     * @return string
     */
    public function afterExecute(GetDefaultDirection $subject, string $result, string $order): string
    {
        if (array_key_exists($order, self::HIGHLIGHT_ORDERS)) {
            return self::HIGHLIGHT_ORDERS[$order];
        }

        return $result;
    }
}
