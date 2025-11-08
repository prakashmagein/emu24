<?php
namespace Swissup\Reviewreminder\Model\Config\Source;

use Swissup\Reviewreminder\Model\Entity as ReminderModel;

class DefaultStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => ReminderModel::STATUS_NEW, 'label' => __('New')),
            array('value' => ReminderModel::STATUS_PENDING, 'label' => __('Pending'))
        );
    }
}
