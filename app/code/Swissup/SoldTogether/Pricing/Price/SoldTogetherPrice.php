<?php

declare(strict_types=1);

namespace Swissup\SoldTogether\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Swissup\SoldTogether\Model\BlockState;
use Swissup\SoldTogether\Model\PromotedPrice;

class SoldTogetherPrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'soldtogether_price';

    /**
     * @var BlockState
     */
    private $blockState;

    /**
     * @var PromotedPrice
     */
    private $promotedPrice;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Product                $saleableItem
     * @param float                  $quantity
     * @param Calculator             $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param Registry               $coreRegistry
     * @param BlockState             $blockState
     * @param PromotedPrice          $promotedPrice
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        Calculator $calculator,
        PriceCurrencyInterface $priceCurrency,
        Registry $coreRegistry,
        BlockState $blockState,
        PromotedPrice $promotedPrice
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->coreRegistry = $coreRegistry;
        $this->blockState = $blockState;
        $this->promotedPrice = $promotedPrice;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (null === $this->value) {
            if ($this->product->hasData(self::PRICE_CODE)) {
                $value = $this->product->getData(self::PRICE_CODE);
                $this->value = $value ? (float)$value : false;
            } else {
                if (in_array($this->blockState->get(), ['rendering_soldtogether_item_order'])) {
                    $promoter = $this->getCurrentProduct();
                    $this->value = $this->promotedPrice->get($this->product, $promoter, 'order');
                    $this->value = $this->value ? (float)$this->value : false;
                }

                $this->value = $this->value ? (float)$this->value : false;
            }

            if ($this->value) {
                $this->value = $this->priceCurrency->convertAndRound($this->value);
            }
        }

        return $this->value;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function getCurrentProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
