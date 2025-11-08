<?php
namespace Swissup\Reviewreminder\Model\Config\Source;

class SpecificOrders implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => __('Any')),
            array('value' => 1, 'label' => __('Specified'))
        );
    }
}
