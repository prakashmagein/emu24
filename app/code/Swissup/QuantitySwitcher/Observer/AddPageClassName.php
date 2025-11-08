<?php

namespace Swissup\QuantitySwitcher\Observer;

use Magento\Framework\View\Page\Config as PageConfig;
use Swissup\QuantitySwitcher\Helper\Data as HelperData;

class AddPageClassName implements \Magento\Framework\Event\ObserverInterface
{
    protected PageConfig $pageConfig;

    protected HelperData $helper;

    public function __construct(
        PageConfig $pageConfig,
        HelperData $helper
    ) {
        $this->pageConfig = $pageConfig;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        $this->pageConfig->addBodyClass("qty-switcher-{$this->helper->getSwitcherType()}");
    }
}
