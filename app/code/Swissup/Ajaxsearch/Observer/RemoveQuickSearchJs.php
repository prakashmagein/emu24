<?php

namespace Swissup\Ajaxsearch\Observer;

use Magento\Framework\Event\ObserverInterface;

class RemoveQuickSearchJs implements ObserverInterface
{
    /**
     * @var \Swissup\Ajaxsearch\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Ajaxsearch\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Ajaxsearch\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Add additional order info to success page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block->getNameInLayout() === 'top.search'
            && $this->helper->isEnabled()
        ) {
            $transport = $observer->getTransport();
            $html = $transport->getHtml();
            $html = preg_replace('/data-mage-init\=\'{\"quickSearch\"(.*)}\'/ims', '', $html);
            $transport->setHtml($html);
        }
    }
}
