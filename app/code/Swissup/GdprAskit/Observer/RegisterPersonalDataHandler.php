<?php

namespace Swissup\GdprAskit\Observer;

class RegisterPersonalDataHandler implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\GdprAskit\Model\AskitDataHandler
     */
    private $handler;

    /**
     * @param \Swissup\GdprAskit\Model\AskitDataHandler $handler
     */
    public function __construct(
        \Swissup\GdprAskit\Model\AskitDataHandler $handler
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
