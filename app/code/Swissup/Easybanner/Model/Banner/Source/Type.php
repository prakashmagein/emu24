<?php
namespace Swissup\Easybanner\Model\Banner\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Banner Type
 */
class Type implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('Banner'), 'value' => 1],
            [ 'label' => __('Lightbox'), 'value' => 2],
            ['label' => __('Awesomebar'), 'value' => 3]
        ];

        return $options;
    }
}
