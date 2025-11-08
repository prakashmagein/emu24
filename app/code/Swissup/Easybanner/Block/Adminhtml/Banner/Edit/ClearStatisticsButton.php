<?php

namespace Swissup\Easybanner\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ClearStatisticsButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Clear Statistics'),
                'class' => 'clear-stats',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getClearUrl() . '\')',
                'sort_order' => 18,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getUrl('*/*/clearStatistics', ['banner_id' => $this->getId()]);
    }
}
