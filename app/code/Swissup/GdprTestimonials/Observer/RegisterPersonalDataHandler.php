<?php

namespace Swissup\GdprTestimonials\Observer;

class RegisterPersonalDataHandler implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\GdprTestimonials\Model\TestimonialDataHandler
     */
    private $handler;

    /**
     * @param \Swissup\GdprTestimonials\Model\TestimonialDataHandler $handler
     */
    public function __construct(
        \Swissup\GdprTestimonials\Model\TestimonialDataHandler $handler
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
