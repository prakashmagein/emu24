<?php

namespace Swissup\Highlight\Model\Config\Source;

class Sortorder implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'default',   'label' => __('Use Default')],
            ['value' => 'position',  'label' => __('Position')],
            ['value' => 'price',     'label' => __('Price')],
            ['value' => 'name',      'label' => __('Name')],
            ['value' => 'entity_id', 'label' => __('Product ID')],
            ['value' => 'random',    'label' => __('Random')],
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
