<?php

namespace Swissup\GdprReviewreminder\Observer;

class RegisterPersonalDataHandler implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\GdprReviewreminder\Model\ReminderDataHandler
     */
    private $handler;

    /**
     * @param \Swissup\GdprReviewreminder\Model\ReminderDataHandler $handler
     */
    public function __construct(
        \Swissup\GdprReviewreminder\Model\ReminderDataHandler $handler
    ) {
        $this->handler = $handler;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()->addItem($this->handler);
    }
}
