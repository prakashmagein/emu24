<?php

namespace Swissup\SoldTogether\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Swissup\SoldTogether\Helper\Request;
use Swissup\SoldTogether\Model\PromotedPrice;
use Swissup\SoldTogether\Pricing\Price\SoldTogetherPrice;

class FrontFinalPriceObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Request
     */
    private $helperRequest;

    /**
     * @var PromotedPrice
     */
    private $promotedPrice;

    /**
     * @param Request       $helperRequest
     * @param PromotedPrice $helperRequest
     */
    public function __construct(
        Request $helperRequest,
        PromotedPrice $promotedPrice
    ) {
        $this->helperRequest = $helperRequest;
        $this->promotedPrice = $promotedPrice;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $promotedPrice = $this->getPromotedPrice($product);
        if ($promotedPrice) {
            $finalPrice = $product->getData(FinalPrice::PRICE_CODE) ?: $promotedPrice;
            $product->setData(
                FinalPrice::PRICE_CODE,
                min($finalPrice, $promotedPrice)
            );
        }

        return $this;
    }

    /**
     * @param  ProductInterface $product
     * @return float
     */
    private function getPromotedPrice(ProductInterface $product)
    {
        if ($product->hasData(SoldTogetherPrice::PRICE_CODE)) {
            return $product->getData(SoldTogetherPrice::PRICE_CODE);
        }

        $id = $product->getParentProductId() ?: $product->getId();
        if (!in_array($id, $this->helperRequest->getRelatedProducts())) {
            return null;
        }

        $relation = (string) $this->helperRequest->getPromotedRelation();
        return $this->promotedPrice->get(
            $product,
            $this->helperRequest->getProduct(),
            $relation
        );
    }
}
