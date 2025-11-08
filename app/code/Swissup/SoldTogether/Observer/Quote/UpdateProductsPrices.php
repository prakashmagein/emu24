<?php

namespace Swissup\SoldTogether\Observer\Quote;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;
use Swissup\SoldTogether\Pricing\Price\SoldTogetherPrice;

class UpdateProductsPrices implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    private $session;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param CheckoutSession   $session
     * @param CollectionFactory $collectionFactory
     * @param Json              $serializer
     */
    public function __construct(
        CheckoutSession $session,
        CollectionFactory $collectionFactory,
        Json $serializer
    ) {
        $this->session = $session;
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $itemOptionsCollection = $this->getItemOptionsCollection();

        $itemOptionsCollection->each([$this, 'updateFinalPrice'], [$productCollection]);
        // update child product for configurable products
        $itemOptionsCollection->each([$this, 'updateChild'], [$productCollection]);
    }

    /**
     * @param  \Magento\Quote\Model\Quote\Item\Option $itemOption
     * @param  ProductCollection                      $productCollection
     * @return void
     */
    public function updateFinalPrice(
        \Magento\Quote\Model\Quote\Item\Option $itemOption,
        ProductCollection $productCollection
    ) {
        if ($itemOption->getCode() !== 'info_buyRequest') {
            return;
        }

        // Find out what product promots current one and what is promoted price
        $data = $this->serializer->unserialize($itemOption->getValue());
        $buyRequest = new \Magento\Framework\DataObject($data);
        $promotedById = $buyRequest->getData('soldtogether/promoted_by');
        $promotedPrice = $buyRequest->getData('soldtogether/promoted_price');
        if (empty($promotedById) || empty($promotedPrice)) {
            return;
        }

        $promotedByProduct = $productCollection->getItemById($promotedById);
        if (!$promotedByProduct) {
            return;
        }

        $product = $productCollection->getItemById($itemOption->getProductId());
        if (!$product) {
            return;
        }

        $finalPrice = $product->getData(FinalPrice::PRICE_CODE) ?: $promotedPrice;
        $product->setData(
            SoldTogetherPrice::PRICE_CODE,
            $promotedPrice
        );
        $product->setData(
            FinalPrice::PRICE_CODE,
            min($finalPrice, $promotedPrice)
        );
    }

    /**
     * @param  \Magento\Quote\Model\Quote\Item\Option $itemOption
     * @param  ProductCollection                      $productCollection
     * @return void
     */
    public function updateChild(
        \Magento\Quote\Model\Quote\Item\Option $itemOption,
        ProductCollection $productCollection
    ) {
        if ($itemOption->getCode() !== 'parent_product_id') {
            return;
        }

        $child = $productCollection->getItemById($itemOption->getProductId());
        $parent = $productCollection->getItemById($itemOption->getValue());
        if ($parent && $child) {
            $promotedPrice = $parent->getData(SoldTogetherPrice::PRICE_CODE);
            if ($promotedPrice) {
                $child->setData(SoldTogetherPrice::PRICE_CODE, $promotedPrice);
                $finalPrice = $child->getData(FinalPrice::PRICE_CODE) ?: $promotedPrice;
                $child->setData(
                    FinalPrice::PRICE_CODE,
                    min($finalPrice, $promotedPrice)
                );
            }
        }
    }

    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection
     */
    private function getItemOptionsCollection()
    {
        $quoteId = $this->session->getQuoteId();
        $collection = $this->collectionFactory->create();
        $collection->join(
                ['qi' => 'quote_item'],
                'main_table.item_id = qi.item_id',
                []
            )
            ->addFieldToFilter('qi.quote_id', $quoteId)
            ->addFieldToFilter('code', [
                'in' => ['info_buyRequest', 'parent_product_id']
            ]);

        return $collection;
    }
}
