<?php

namespace Swissup\Askit\Model\Config\Source;

class QuestionsPageStatus implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Disable')],
            ['value' => 1, 'label' => __('Enable')],
            ['value' => 2, 'label' => __('Enable when entity has questions')],
        ];
    }
}
