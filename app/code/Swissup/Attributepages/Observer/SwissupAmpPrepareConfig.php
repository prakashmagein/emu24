<?php

namespace Swissup\Attributepages\Observer;

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
        $optionArray['attributepages'] = __('Attribute Pages');
        $pages->setData($optionArray);
    }
}
