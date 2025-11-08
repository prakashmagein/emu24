<?php
namespace Swissup\Easybanner\Model\Banner\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('Image'), 'value' => 'image'],
            ['label' => __('HTML Content'), 'value' => 'html'],
        ];

        return $options;
    }
}
