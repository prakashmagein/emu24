<?php
namespace Swissup\Reviewreminder\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckOrderAfterSaveObserver implements ObserverInterface
{
    /**
     * @var \Swissup\Reviewreminder\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Swissup\Reviewreminder\Helper\Helper
     */
    protected $helper;
    /**
     * @var \Swissup\Reviewreminder\Model\EntityFactory
     */
    protected $reminderFactory;
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Swissup\Reviewreminder\Model\UnsubscribeFactory
     */
    protected $unsubscribeFactory;

    /**
     * @param \Swissup\Reviewreminder\Helper\Config $configHelper
     * @param \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Swissup\Reviewreminder\Model\UnsubscribeFactory $unsubscribeFactory
     */
    public function __construct(
        \Swissup\Reviewreminder\Helper\Config $configHelper,
        \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory,
        \Swissup\Reviewreminder\Helper\Helper $helper,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Swissup\Reviewreminder\Model\UnsubscribeFactory $unsubscribeFactory
    ) {
        $this->configHelper = $configHelper;
        $this->reminderFactory = $reminderFactory;
        $this->helper = $helper;
        $this->mathRandom = $mathRandom;
        $this->orderRepository = $orderRepository;
        $this->unsubscribeFactory = $unsubscribeFactory;
    }
    /**
     * Check order status and save it to review reminder table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $storeId = $order->getStoreId();

        if (!$this->configHelper->isEnabled($storeId)) {
            return $this;
        }

        if (!$this->configHelper->isEnabledForGuests($storeId) && $order->getCustomerIsGuest()) {
            return $this;
        }

        // fix for empty email
        if (!$order->getCustomerEmail()) {
            $newOrder = $this->orderRepository->get($order->getId());
            $order->setCustomerEmail($newOrder->getCustomerEmail());
        }

        // skip unsubscribed email
        if ($this->isUnsubscribed($order->getCustomerEmail())) {
            return $this;
        }

        if ($this->configHelper->allowSpecificStatuses()) {
            $orderStatus = $order->getStatus();
            $allowedStatuses = $this->configHelper->specificOrderStatuses();
            if (in_array($orderStatus, $allowedStatuses)) {
                $this->saveOrder($order);
            }
        } else {
            $this->saveOrder($order);
        }
    }

    /**
     * Check if email is unsubscribed
     * @param  string $email
     * @return boolean
     */
    protected function isUnsubscribed($email)
    {
        $unsubscribeModel = $this->unsubscribeFactory->create()->load($email, 'email');

        return !!$unsubscribeModel->getId();
    }

    /**
     * Check order id and save it to review reminder table
     */
    protected function saveOrder($order)
    {
        $storeId = $order->getStoreId();
        $model = $this->reminderFactory->create();
        $collection = $model->getCollection()
            ->addFieldTofilter('order_id', $order->getId());
        if ($collection->getSize() == 0) {
            $model->setOrderId($order->getId());
            $model->setCustomerEmail($order->getCustomerEmail());
            $model->setStatus($this->configHelper->getDefaultStatus($storeId));
            $model->setOrderDate($this->helper->getOrderDate($order, $this->configHelper));
            $model->setHash($this->mathRandom->getRandomString(16));
            $model->save();
        }
    }
}
