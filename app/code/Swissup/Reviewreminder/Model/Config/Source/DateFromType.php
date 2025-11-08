<?php
namespace Swissup\Reviewreminder\Model\Config\Source;

class DateFromType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => __('Last year')),
            array('value' => 2, 'label' => __('Last month')),
            array('value' => 3, 'label' => __('Last week')),
            array('value' => 4, 'label' => __('From custom date'))
        );
    }
}
