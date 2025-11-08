<?php

namespace Swissup\Navigationpro\Block\Adminhtml\Menu\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Back extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'class' => 'back',
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
            'sort_order' => 10
        ];
    }
}
