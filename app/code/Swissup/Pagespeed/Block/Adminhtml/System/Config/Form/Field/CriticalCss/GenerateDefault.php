<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field\CriticalCss;

use Swissup\Codemirror\Block\Adminhtml\System\Config\Form\Field\Editor;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class GenerateDefault extends Editor
{
    /**
     * @var string
     */
    protected $_template = 'Swissup_Codemirror::form/field/editor.phtml';

    /**
     *
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
        array $data = [
            'editor_config' => [
                'mode' => 'css',
                'line_wrapping' => true,
                'css_class' => 'cm-break-word'
            ]
        ]
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
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
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return parent::_getElementHtml($element)
            . '<p class="note">'
//            .  $this->getGenerateButtonHtml() //. '<br/>'
            .  $this->getExtraLinksHtml()
            . '</p>';
    }

    private function getExtraLinksHtml()
    {
        $url = $this->getStoreBaseUrl();
        $apiUrl = 'https://pagespeed.swissuplabs.com/critical-css/generate?website=' . urlencode($url);

        return '<div>' .
            '<a href="' . $apiUrl . '" target="_blank" rel="noopener">' .
            $this->escapeHtml(__('Get your site critical css manually.')) .
        '</a></div>';
    }

    private function getGenerateButtonHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $request = $buttonBlock->getRequest();
        $params = [
            'website' => $request->getParam('website'),
            'store' => $request->getParam('store', 0)
        ];

        $url = $this->getUrl('swissup_pagespeed/criticalcss/generateDefault', $params);
        $data = [
            'id' => 'generate_default_critical_css',
            'class' => 'swissup-pagespeed-button',
            'label' => __('Generate default critical css'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }
}
