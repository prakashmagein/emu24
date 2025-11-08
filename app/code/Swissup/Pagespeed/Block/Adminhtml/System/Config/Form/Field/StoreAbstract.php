<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

abstract class StoreAbstract extends Field
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * GettingStarted constructor.
     *
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function getStoreBaseUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $store = $this->storeManager->getStore($storeId);

        return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
}
