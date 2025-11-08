<?php
/**
 * Copyright © 2019 Swissup. All rights reserved.
 */
namespace Swissup\Ajaxsearch\Block;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     *
     * @var \Swissup\Ajaxsearch\Helper\Data
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Swissup\Ajaxsearch\Helper\Data $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Ajaxsearch\Helper\Data $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->configHelper->isEnabled() === false) {
            return '';
        }

        return parent::_toHtml();
    }
}
