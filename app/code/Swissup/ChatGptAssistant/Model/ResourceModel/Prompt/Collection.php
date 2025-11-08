<?php
namespace Swissup\ChatGptAssistant\Model\ResourceModel\Prompt;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\ChatGptAssistant\Model\Prompt::class,
            \Swissup\ChatGptAssistant\Model\ResourceModel\Prompt::class
        );
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'name');
    }

    /**
     * Filter collection by status
     *
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->getSelect()
            ->where('main_table.status = ?', $status);

        return $this;
    }
}
