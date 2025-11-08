<?php

namespace Swissup\SoldTogether\Observer\Quote;

use Magento\Framework\Serialize\Serializer\Json;

class CollectTotalsOnRemoveItem implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ){
        $this->serializer = $serializer;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $triggerRecollect = false;
        $removedItem = $observer->getQuoteItem();
        $quote = $removedItem->getQuote();
        if (is_iterable($quote)) {
            foreach ($quote->getItems() as $item) {
                $itemOption = $item->getOptionByCode('info_buyRequest');
                $data = $this->serializer->unserialize($itemOption->getValue());
                $buyRequest = new \Magento\Framework\DataObject($data);
                if ($buyRequest->getData('soldtogether/promoted_by') == $removedItem->getProductId()) {
                    $triggerRecollect = true;
                    $buyRequest->unsetData('soldtogether');
                    $itemOption->setValue($buyRequest->toJson())->save();
                }
            }

            if ($triggerRecollect) {
                $quote->setTriggerRecollect(1)->save();
            }
        }

    }
}
