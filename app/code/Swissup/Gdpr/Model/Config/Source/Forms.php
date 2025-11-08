<?php

namespace Swissup\Gdpr\Model\Config\Source;

class Forms implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection
     */
    private $forms;

    /**
     * @param \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms
     */
    public function __construct(
        \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms
    ) {
        $this->forms = $forms;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->forms->toOptionArray() as $option) {
            $result[] = [
                'label' => __($option['label']),
                'value' => __($option['value']),
            ];
        }
        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }
}
