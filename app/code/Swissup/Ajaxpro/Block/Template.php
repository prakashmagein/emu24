<?php
/**
 * Copyright © 2016-2020 Swissup. All rights reserved.
 */
namespace Swissup\Ajaxpro\Block;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    protected $configHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Ajaxpro\Helper\Config $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->configHelper->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
