<?php

namespace Swissup\Easybanner\Model\Placeholder\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SortMode implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('By Banners Sort Order'), 'value' => 'sort_order'],
            ['label' => __('Random'), 'value' => 'random']
        ];
        return $options;
    }
}
