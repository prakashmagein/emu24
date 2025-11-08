<?php

namespace Swissup\EasySlide\Model\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Backend\Block\DataProviders\ImageUploadConfig as OriginImageUploadConfig;

class ImageUploadConfig extends OriginImageUploadConfig implements ArgumentInterface
{
    /**
     * @var boolean
     */
    protected $isResizeEnabled = false;

    /**
     * Get slide image resize configuration
     *
     * @return int
     */
    public function getIsResizeEnabled(): int
    {
        return (int) $this->isResizeEnabled;
    }
}
