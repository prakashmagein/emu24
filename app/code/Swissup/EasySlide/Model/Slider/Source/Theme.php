<?php

namespace Swissup\EasySlide\Model\Slider\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Theme implements OptionSourceInterface
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
                'value' => '',
                'label' => __('Default')
            ],
            [
                'value' => 'black',
                'label' => __('Black')
            ],
            [
                'value' => 'white',
                'label' => __('White')
            ]
        ];

        return $options;
    }
}
