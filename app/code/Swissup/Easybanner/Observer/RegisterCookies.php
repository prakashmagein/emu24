<?php

namespace Swissup\Easybanner\Observer;

class RegisterCookies implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Easybanner\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Easybanner\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Easybanner\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()->addItemFromArray([
            'name' => $this->helper->getCookieName(),
            'description' => "Preserves the visitor's preferences and stats regarding shown popup blocks.",
            'group' => 'preferences',
        ]);
    }
}
