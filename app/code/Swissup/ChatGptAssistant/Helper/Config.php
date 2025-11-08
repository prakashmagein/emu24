<?php
namespace Swissup\ChatGptAssistant\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * Path to store config module enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'swissup_chatgpt_assistant/general/enabled';

    /**
     * Path to store config OpenAI API key
     *
     * @var string
     */
    const XML_PATH_OPENAI_API_KEY = 'swissup_chatgpt_assistant/general/openai_api_key';

    /**
     * Path to store config OpenAI model
     *
     * @var string
     */
    const XML_PATH_OPENAI_MODEL = 'swissup_chatgpt_assistant/general/openai_model';

    /**
     * Get store config value
     *
     * @param  string $key
     * @return mixed
     */
    protected function _getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool)$this->_getConfig(self::XML_PATH_ENABLED);
    }

    /**
     * Get OpenAI API key
     *
     * @return string
     */
    public function getOpenaiApiKey()
    {
        return (string)$this->_getConfig(self::XML_PATH_OPENAI_API_KEY);
    }

    /**
     * Get OpenAI model
     *
     * @return string
     */
    public function getOpenaiModel()
    {
        return (string)$this->_getConfig(self::XML_PATH_OPENAI_MODEL);
    }
}
