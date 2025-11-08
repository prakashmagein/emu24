<?php

namespace Swissup\Attributepages\Observer;

class SwissupAmpPageSupported implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result = $observer->getResult();
        $page = $result->getCurrentPage();
        $supportedPages = $result->getSupportedPages();

        if (0 === strpos($page, 'attributepages_') && in_array('attributepages', $supportedPages)) {
            $result->setIsPageSupported(true);
        }
    }
}
