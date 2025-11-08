<?php

namespace Swissup\Navigationpro\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Menu extends Template implements BlockInterface
{
    /**
     * Do not allow to change template for this block.
     * Template variable will be passed into 'menu' child.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'Swissup_Navigationpro::widget.phtml';
    }

    /**
     * @return bool
     */
    public function canUseWrapper()
    {
        return (bool) $this->getData('wrap');
    }

    protected function _beforeToHtml()
    {
        // Move widget options into Block\Menu block
        $data = $this->getData();
        unset($data['type']);
        unset($data['module_name']);

        $this->addChild('menu', 'Swissup\Navigationpro\Block\Menu', $data);

        return parent::_beforeToHtml();
    }
}
