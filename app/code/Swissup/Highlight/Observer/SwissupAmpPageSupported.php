<?php

namespace Swissup\Highlight\Observer;

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

        if (0 === strpos($page, 'highlight') && in_array('highlight', $supportedPages)) {
            $result->setIsPageSupported(true);
        }
    }
}
