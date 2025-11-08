<?php

namespace Swissup\SoldTogether\Plugin\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider as Subject;

class LowestPriceOptionsProvider
{
    public function afterGetProducts(
        Subject $subject,
        $result,
        ProductInterface $product
    ) {
        if (is_array($result) && $product->hasData('soldtogether_data')) {
            $soldtogetherData = $product->getData('soldtogether_data');
            array_map(function ($subProduct) use ($soldtogetherData) {
                $subProduct->setData('soldtogether_data', $soldtogetherData);
            }, $result);
        }

        return $result;
    }
}
