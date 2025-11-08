<?php
namespace Swissup\Easybanner\Model\Banner\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Target implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('Same Tab'), 'value' => 'self'],
            ['label' => __('New Tab'), 'value' => 'blank'],
            ['label' => __('Popup Window'), 'value' => 'popup'],
        ];

        return $options;
    }
}
