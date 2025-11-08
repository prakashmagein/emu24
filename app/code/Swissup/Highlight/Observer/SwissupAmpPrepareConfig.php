<?php

namespace Swissup\Highlight\Observer;

class SwissupAmpPrepareConfig implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $pages = $observer->getPages();
        $optionArray = $pages->getData();
        $optionArray['highlight'] = __('Highlight Pages');
        $pages->setData($optionArray);
    }
}
