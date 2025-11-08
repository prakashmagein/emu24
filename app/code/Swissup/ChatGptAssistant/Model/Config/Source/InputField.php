<?php
namespace Swissup\ChatGptAssistant\Model\Config\Source;

use Swissup\ChatGptAssistant\Model\ResourceModel\InputField\CollectionFactory;

class InputField implements \Magento\Framework\Data\OptionSourceInterface
{
    private CollectionFactory $fieldCollectionFactory;

    public function __construct(CollectionFactory $fieldCollectionFactory)
    {
        $this->fieldCollectionFactory = $fieldCollectionFactory;
    }

    /**
     * Get options with empty value
     *
     * @return array
     */
    public function toOptionArray()
    {
        $fieldCollection = $this->fieldCollectionFactory->create();

        $empty = ['label' => __('No Default Field'), 'value' => ''];
        $options = $fieldCollection->toOptionArray();

        array_unshift($options, $empty);

        return $options;
    }
}
