<?php

namespace Swissup\EasySlide\Model\Slider\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Direction implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'horizontal',
                'label' => __('Horizontal')
            ],
            [
                'value' => 'vertical',
                'label' => __('Vertical')
            ]
        ];

        return $options;
    }
}
