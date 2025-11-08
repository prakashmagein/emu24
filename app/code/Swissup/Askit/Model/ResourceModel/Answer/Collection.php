<?php
namespace Swissup\Askit\Model\ResourceModel\Answer;

class Collection extends \Swissup\Askit\Model\ResourceModel\Message\Collection
{
    /**
     * Add filter by only ready fot sending item
     *
     * @return $this
     */
    public function addParentIdFilter($parentId)
    {
        $this->getSelect()->where('main_table.parent_id = ?', $parentId);
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function addFirstItemData()
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getTable('swissup_askit_item'),
                ['message_id', 'item_id', 'item_type_id']
            );
        $data = $this->getConnection()->fetchAssoc($select);
        $keys = ['item_id', 'item_type_id'];
        /** @var \Swissup\Askit\Model\Item $item */
        foreach ($this->_items as $messageId => $item) {
            $questionId = $item->getData('parent_id');
            foreach ($keys as $key) {
                if (isset($data[$questionId][$key])) {
                    $value = (int) $data[$questionId][$key];
                    $item->setData($key, $value);
                }
            }
        }
        return $this;
    }
}
