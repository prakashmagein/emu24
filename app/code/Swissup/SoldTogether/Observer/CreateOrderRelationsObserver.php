<?php
namespace Swissup\SoldTogether\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Swissup\SoldTogether\Model\CronJobFactory;

class CreateOrderRelationsObserver implements ObserverInterface
{
    /**
     * @var CronJobFactory
     */
    private $cronJobFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CronJobFactory       $cronJobFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CronJobFactory $cronJobFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cronJobFactory = $cronJobFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Create order relations
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer): self
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->isCreateRelationOnNewOrder()) {
            $cronJob = $this->cronJobFactory->create();
            $cronJob->setData('data', ['order_id' => $order->getId()]);
            $cronJob->save();
        }

        return $this;
    }

    private function isCreateRelationOnNewOrder(): bool
    {
        $isOrderRelationCreate = $this->scopeConfig->isSetFlag(
            'soldtogether/relations/order_on_order_create'
        );
        $isCustomerRelationCreate = $this->scopeConfig->isSetFlag(
            'soldtogether/relations/customer_on_order_create'
        );

        return $isOrderRelationCreate || $isCustomerRelationCreate;
    }
}
