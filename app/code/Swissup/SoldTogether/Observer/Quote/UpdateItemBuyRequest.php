<?php

namespace Swissup\SoldTogether\Observer\Quote;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Swissup\SoldTogether\Helper\Request;
use Swissup\SoldTogether\Model\PromotedPrice;

class UpdateItemBuyRequest implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Request
     */
    private $helperRequest;

    /**
     * @var PromotedPrice
     */
    private $promotedPrice;

    /**
     * @param Json          $serializer
     * @param Request       $helperRequest
     * @param PromotedPrice $promotedPrice
     */
    public function __construct(
        Json $serializer,
        Request $helperRequest,
        PromotedPrice $promotedPrice
    ) {
        $this->serializer = $serializer;
        $this->helperRequest = $helperRequest;
        $this->promotedPrice = $promotedPrice;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $observer->getEvent()->getItems();
        array_walk($items, [$this, 'updateBuyRequest']);
    }

    /**
     * @param  Item $item
     */
    public function updateBuyRequest(Item $item) {
        $product = $item->getProduct();
        $relatedIds = $this->helperRequest->getRelatedProducts();
        $promotedRelation = $this->helperRequest->getPromotedRelation();
        if (in_array($product->getId(), $relatedIds)
            && $promotedRelation
        ) {
            $buyRequest = $item->getOptionByCode('info_buyRequest');
            $promoter = $this->helperRequest->getProduct();
            $promotedPrice = $this->promotedPrice->get($product, $promoter, $promotedRelation);
            if ($promotedPrice) {
                $data = $this->serializer->unserialize($buyRequest->getValue());
                $data['soldtogether'] = [
                    'promoted_by' => $promoter,
                    'promoted_price' => $promotedPrice,
                    'promoted_relation' => $promotedRelation
                ];
                $buyRequest->setValue($this->serializer->serialize($data));
            }
        }
    }
}
