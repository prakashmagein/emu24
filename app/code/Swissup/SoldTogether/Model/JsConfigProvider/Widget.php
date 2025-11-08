<?php

namespace Swissup\SoldTogether\Model\JsConfigProvider;

use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\Element\Template;
use Swissup\SoldTogether\Block\Order as BlockFrequentlyBoughtTogether;
use Swissup\SoldTogether\Block\Customer as CustomersAlsoBuy;

class Widget
{
    private FormatInterface $localeFormat;

    public function __construct(
        FormatInterface $localeFormat
    ) {
        $this->localeFormat = $localeFormat;
    }

    public function getConfig(Template $ctxBlock): array {
        if ($this->isFrequentlyBoughtTogetherBlock($ctxBlock)) {
            return [
                'Swissup_SoldTogether/js/frequently-bought-together' => [
                    'taxDisplay' => $ctxBlock->getTaxDisplayConfig(),
                    'priceFormat' => $this->localeFormat->getPriceFormat(),
                    'mainProductPriceBox' => $ctxBlock->getMainProductPriceBox() ?: '.product-info-price [data-role=priceBox], .bundle-info [data-role=priceBox]'
                ]
            ];
        }

        if ($this->isCustomersAlsoBuy($ctxBlock)) {
            return [
                'Swissup_SoldTogether/js/customer-also-bought' => [
                    'tocartForm' => '#product_addtocart_form'
                ]
            ];
        }

        return [];
    }

    public function isFrequentlyBoughtTogetherBlock(Template $block): bool
    {
        return strpos($block::class, BlockFrequentlyBoughtTogether::class) === 0;
    }

    public function isCustomersAlsoBuy(Template $block): bool
    {
        return strpos($block::class, CustomersAlsoBuy::class) === 0;
    }
}
