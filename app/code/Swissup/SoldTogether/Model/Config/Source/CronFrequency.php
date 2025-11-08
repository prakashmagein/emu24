<?php

namespace Swissup\SoldTogether\Model\Config\Source;

class CronFrequency implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '* * * * *', 'label' => __('Every minute')],
            ['value' => '*/5 * * * *', 'label' => __('Every 5 minutes')],
            ['value' => '*/15 * * * *', 'label' => __('Every 15 minutes')],
            ['value' => '*/30 * * * *', 'label' => __('Every half an hour')],
            ['value' => '0 * * * *', 'label' => __('Every hour')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
}
