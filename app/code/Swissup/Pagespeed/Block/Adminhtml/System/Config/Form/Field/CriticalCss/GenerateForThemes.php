<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field\CriticalCss;

class GenerateForThemes extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * GettingStarted constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed[] $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = [],
        ?\Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->storeManager = $storeManager;
    }

    /**
     *
     * @return string
     */
    protected function getStoreBaseUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $store = $this->storeManager->getStore($storeId);

        return $store->getBaseUrl();
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getGenerateButtonHtml();
    }

    private function getGenerateButtonHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $request = $buttonBlock->getRequest();
        $params = [
            'website' => $request->getParam('website', 0),
            'group' => $request->getParam('group', 0),
            'store' => $request->getParam('store', 0)
        ];
        $url = $this->getUrl('swissup_pagespeed/criticalcss/generateForThemes', $params);
        $data = [
            'id' => 'generate_themes_critical_css',
            'class' => 'swissup-pagespeed-button',
            'label' => __('Generate critical css for theme(s)'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }
}
