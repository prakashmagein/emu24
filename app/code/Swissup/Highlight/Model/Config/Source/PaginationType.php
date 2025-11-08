<?php

namespace Swissup\Highlight\Model\Config\Source;

class PaginationType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_DEFAULT = 'default';
    const TYPE_IMPROVED = 'improved';

    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_DEFAULT,   'label' => __('Default (Slow for large catalogs)')],
            ['value' => self::TYPE_IMPROVED,  'label' => __('Improved performance')],
        ];
    }

    public function toArray()
    {
        $result = array();
        foreach ($this->toOptionArray() as $values) {
            $result[$values['value']] = $values['label'];
        }
        return $result;
    }
}
