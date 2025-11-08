<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Fblike\Model\Config\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => "standard", 'label' => __('Standard')],
            ['value' => "button_count", 'label' => __('Button Count')],
            ['value' => "button", 'label' => __('Button')],
            ['value' => "box_count", 'label' => __('Box Count')],
            ['value' => "custom", 'label' => __('Custom Button')]
        ];
        return $result;
    }
}
