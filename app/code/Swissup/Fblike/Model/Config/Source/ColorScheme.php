<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Fblike\Model\Config\Source;

class ColorScheme implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => "light", 'label' => __('Light')],
            ['value' => "dark", 'label' => __('Dark')]
        ];
        return $result;
    }
}
