<?php

namespace Swissup\Hreflang\Model\Config\Source;

class ValueStrategy implements \Magento\Framework\Option\ArrayInterface
{
    const STOREVIEW_LOCALE = 'storeview_locale';
    const CUSTOM_VALUE = 'custom_value';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::STOREVIEW_LOCALE,
                'label' => __('Store View locale')
            ],
            [
                'value' => self::CUSTOM_VALUE,
                'label' => __('Custom value')
            ]
        ];

        return $options;
    }
}
