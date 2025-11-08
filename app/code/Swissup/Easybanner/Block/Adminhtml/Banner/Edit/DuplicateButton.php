<?php

namespace Swissup\Easybanner\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Duplicate'),
                'on_click' => sprintf("setLocation('%s')", $this->getDuplicateUrl()),
                'class' => 'duplicate',
                'sort_order' => 25,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', ['banner_id' => $this->getId()]);
    }
}
