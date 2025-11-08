<?php

namespace Swissup\Gdpr\Model\Config\Source;

class CookieBarDisplayMode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'full', 'label' => __('Full — ability to select which cookie groups to allow.')],
            ['value' => 'minimalistic', 'label' => __('Minimalistic — ability to allow all cookie groups only.')],
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
