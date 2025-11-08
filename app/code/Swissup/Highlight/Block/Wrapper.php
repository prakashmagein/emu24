<?php

namespace Swissup\Highlight\Block;

use Magento\Framework\View\Element\Template;

class Wrapper extends Template
{
    protected $_template = 'Swissup_Highlight::block.phtml';

    public function getTemplate()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array('breeze_theme', $handles)) {
            return 'Swissup_Highlight::block-breeze.phtml';
        }

        return parent::getTemplate();
    }
}
