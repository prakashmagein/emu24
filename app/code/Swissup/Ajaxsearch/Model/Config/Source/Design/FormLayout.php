<?php

namespace Swissup\Ajaxsearch\Model\Config\Source\Design;

class FormLayout implements \Magento\Framework\Data\OptionSourceInterface
{
    const DEFAULT           = 0;
    const FOLDED_INLINE     = 1;
    const FOLDED_FULLSCREEN = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DEFAULT,           'label' => __('Default - Initially visible form')],
            ['value' => self::FOLDED_INLINE,     'label' => __('Icon only - Minimalistic form')],
            ['value' => self::FOLDED_FULLSCREEN, 'label' => __('Icon only - Fullscreen form')],
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
