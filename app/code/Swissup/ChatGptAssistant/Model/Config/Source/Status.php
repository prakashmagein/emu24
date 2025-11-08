<?php
namespace Swissup\ChatGptAssistant\Model\Config\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    private \Swissup\ChatGptAssistant\Model\Prompt $prompt;

    public function __construct(\Swissup\ChatGptAssistant\Model\Prompt $prompt)
    {
        $this->prompt = $prompt;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->prompt->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
