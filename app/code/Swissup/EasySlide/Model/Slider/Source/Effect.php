<?php

namespace Swissup\EasySlide\Model\Slider\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Effect implements OptionSourceInterface
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
                'value' => 'slide',
                'label' => __('Slide')
            ],
            [
                'value' => 'fade',
                'label' => __('Fade')
            ],
            [
                'value' => 'cube',
                'label' => __('Cube')
            ],
            // [
            //     'value' => 'Coverflow',
            //     'label' => __('Coverflow')
            // ],
            [
                'value' => 'flip',
                'label' => __('Flip')
            ]
        ];

        return $options;
    }
}
