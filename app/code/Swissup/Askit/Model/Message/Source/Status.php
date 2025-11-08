<?php
namespace Swissup\Askit\Model\Message\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Swissup\Askit\Api\Data\MessageInterface;

class Status implements OptionSourceInterface
{
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            MessageInterface::STATUS_PENDING     => __('Pending'),
            MessageInterface::STATUS_APPROVED    => __('Approved'),
            MessageInterface::STATUS_DISAPPROVED => __('Disapproved'),
            MessageInterface::STATUS_CLOSE       => __('Close')
        ];
    }

    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve option array with empty value
     *
     * @return array<int, array<string, int|string>>
     */
    public function getAllOptions()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
