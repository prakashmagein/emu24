<?php

namespace Swissup\SoldTogetherEmail\Model\Resolver\DataProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class Products extends \Swissup\SoldTogether\Model\Resolver\DataProvider\Products
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @var array
     */
    private $productIds;

    /**
     * @param int|string $orderId
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIds() {
        if (!$this->productIds) {
            $objectManager = ObjectManager::getInstance();
            $orderRepo = $objectManager->get(OrderRepositoryInterface::class);
            $order = $orderRepo->get($this->orderId);
            $this->productIds = array_map(function ($item) {
                return $item->getProductId();
            }, $order->getAllVisibleItems());
            $this->setCurrentProductId(reset($this->productIds));
        }

        return $this->productIds;
    }
}