<?php

namespace Swissup\ImageOptimizer\Model\Config\Source\Image\Optimize;

class Provider implements \Magento\Framework\Data\OptionSourceInterface
{
    const LOCAL  = 0;
    const REMOTE = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LOCAL,  'label' => __('Local Binaries')],
            ['value' => self::REMOTE, 'label' => __('Remote API')],
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
