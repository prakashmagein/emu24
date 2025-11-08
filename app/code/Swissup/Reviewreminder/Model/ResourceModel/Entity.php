<?php
namespace Swissup\Reviewreminder\Model\ResourceModel;

/**
 * Reviewreminder Entity mysql resource
 */
class Entity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_reviewreminder_entity', 'entity_id');
    }

    public function getOrderInfo($id)
    {
        $adapter = $this->getConnection();

        $productSelect = $adapter->select()
            ->from($this->getTable('sales_order_item'), "GROUP_CONCAT(' ', name)")
            ->where('order_id = ?', $id)
            ->where('product_type != ?', 'configurable');

        $customerNameSelect = $adapter->select()
            ->from($this->getTable('sales_order_address'), "CONCAT(firstname, ' ', lastname)")
            ->where('parent_id = ?', $id)
            ->where('address_type = ?', 'billing');

        $select = $adapter->select()
            ->from($this->getTable('sales_order'),
                array(
                    'increment_id' => 'increment_id',
                    'customer_name' => $customerNameSelect,
                    'products' => $productSelect
                )
            )
            ->where('entity_id = ?', $id);

        return $adapter->fetchRow($select);
    }
}
