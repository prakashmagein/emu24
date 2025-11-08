<?php

namespace Swissup\QuantitySwitcher\Block;

use Magento\Framework\View\Element\Template;
use Swissup\QuantitySwitcher\Helper\Data as HelperData;

class Init extends Template
{
    private HelperData $helper;

    public function __construct(
        Template\Context $context,
        HelperData $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getQuantitySwitcherHelper()
    {
        return $this->helper;
    }
}
