<?php

namespace Swissup\Easybanner\Block\Adminhtml\Banner\Listing;

use Swissup\Easybanner\Block\Adminhtml\AbstractButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ClearStatisticsButton extends AbstractButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Clear Statistics'),
            'class' => 'clear-stats',
            'on_click' => 'deleteConfirm(\'' . __(
                'Are you sure you want to do this?'
            ) . '\', \'' . $this->getClearUrl() . '\')',
            'sort_order' => 18,
        ];
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getUrl('*/*/clearStatistics', ['banner_id' => 'all']);
    }
}
