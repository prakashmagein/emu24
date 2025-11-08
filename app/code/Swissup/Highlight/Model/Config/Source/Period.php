<?php

namespace Swissup\Highlight\Model\Config\Source;

class Period implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'P1Y',  'label' => __('1 Year')],
            ['value' => 'P6M',  'label' => __('6 Months')],
            ['value' => 'P3M',  'label' => __('3 Months')],
            ['value' => 'P2M',  'label' => __('2 Months')],
            ['value' => 'P1M',  'label' => __('1 Month')],
            ['value' => 'P14D', 'label' => __('2 Weeks')],
            ['value' => 'P7D',  'label' => __('1 Week')],
            ['value' => 'P3D',  'label' => __('3 Days')],
            ['value' => 'P1D',  'label' => __('1 Day')],
            ['value' => 'PT8H', 'label' => __('8 Hours')],
            ['value' => 'PT1H', 'label' => __('1 Hour')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this->toOptionArray() as $values) {
            $result[$values['value']] = $values['label'];
        }
        return $result;
    }
}
