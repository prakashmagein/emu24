<?php
namespace Swissup\Askit\Model\ResourceModel;

/**
 * Askit Vote mysql resource
 */
class Vote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_askit_vote', 'id');
    }

    /**
     *
     * @param int $id
     * @param int $customerId
     * @return bool
     */
    public function isVoted($id, $customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select = $select//$this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('message_id = ?', $id)
            ->where('customer_id = ?', $customerId);
        $row = $connection->fetchCol($select);
        return count($row) == 0 ? false : true;
    }
}
