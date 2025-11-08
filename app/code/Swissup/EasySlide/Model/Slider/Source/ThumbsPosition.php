<?php

namespace Swissup\EasySlide\Model\Slider\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ThumbsPosition implements OptionSourceInterface
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
                'value' => 'above',
                'label' => __('Above slider')
            ],
            [
                'value' => 'right',
                'label' => __('Right side')
            ],
            [
                'value' => 'under',
                'label' => __('Under slider')
            ],
            [
                'value' => 'left',
                'label' => __('Left side')
            ]
        ];

        return $options;
    }
}
