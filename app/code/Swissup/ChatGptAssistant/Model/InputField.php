<?php

namespace Swissup\ChatGptAssistant\Model;

class InputField extends \Magento\Framework\DataObject
{
    /**
     * Add prompt
     *
     * @param array $prompt
     */
    public function addPrompt(array $prompt)
    {
        $jsConfig = $this->getJsConfig();
        if (!isset($jsConfig['prompts'])) {
            $jsConfig['prompts'] = [];
        }

        $jsConfig['prompts'][] = $prompt;
        $this->setJsConfig($jsConfig);

        return $this;
    }

    /**
     * Get prompts list
     *
     * @return array
     */
    public function getPrompts()
    {
        $jsConfig = $this->getJsConfig();
        if (!$jsConfig || empty($jsConfig['prompts'])) {
            return [];
        }

        $result = [];
        foreach ($jsConfig['prompts'] as $prompt) {
            $result[] = $prompt;
        }

        return $result;
    }
}
