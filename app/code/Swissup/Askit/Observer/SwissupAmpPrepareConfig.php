<?php

namespace Swissup\Askit\Observer;

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
        $optionArray['askit_index_index'] = __('Askit Questions Page');
        $pages->setData($optionArray);
    }
}
