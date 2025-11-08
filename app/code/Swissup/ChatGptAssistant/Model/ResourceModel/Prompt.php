<?php
namespace Swissup\ChatGptAssistant\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Prompt extends AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_chatgptassistant_prompt', 'entity_id');
    }
}
