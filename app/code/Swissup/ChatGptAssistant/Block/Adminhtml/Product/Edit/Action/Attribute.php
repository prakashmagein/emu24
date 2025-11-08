<?php
namespace Swissup\ChatGptAssistant\Block\Adminhtml\Product\Edit\Action;

class Attribute extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute
{
    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        $helper = $this->_helperActionAttribute;
        return $this->getUrl('swissup_assistant/*/save', ['store' => $helper->getSelectedStoreId()]);
    }

    /**
     * Get validation url
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return '';
    }
}
