<?php

namespace Swissup\Navigationpro\Block\Adminhtml\Menu\Edit\Button;

class Duplicate extends Generic
{
    public function getButtonData()
    {
        $data = [];

        if ($this->getMenuId()) {
            $data = [
                'label' => __('Duplicate Menu'),
                'class' => 'duplicate',
                'on_click' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('*/menu/duplicate', ['menu_id' => $this->getMenuId()]);
    }
}
