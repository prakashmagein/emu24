<?php
namespace Swissup\Askit\Model\Message\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Swissup\Askit\Api\Data\MessageInterface;

class PublicStatuses implements OptionSourceInterface
{
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            MessageInterface::STATUS_APPROVED => __('Approved'),
            MessageInterface::STATUS_CLOSE    => __('Close')
        ];
    }

    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
