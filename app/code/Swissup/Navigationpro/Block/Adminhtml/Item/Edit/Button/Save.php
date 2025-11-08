<?php

namespace Swissup\Navigationpro\Block\Adminhtml\Item\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getItemId()) {
            return [];
        }

        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90
        ];
    }
}
