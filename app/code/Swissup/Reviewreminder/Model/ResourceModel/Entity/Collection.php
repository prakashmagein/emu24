<?php
namespace Swissup\Reviewreminder\Model\ResourceModel\Entity;
/**
 * Reminders Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Reviewreminder\Model\Entity', 'Swissup\Reviewreminder\Model\ResourceModel\Entity');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['status'] = 'main_table.status';

        $this->addFilterToMap(
            'customer_email',
            'main_table.customer_email'
        );
        $this->addFilterToMap(
            'customer_name',
            new \Zend_Db_Expr('(SELECT CONCAT(y.firstname, \' \', y.lastname)
                                FROM ' . $this->getTable('sales_order_address') . ' y
                                WHERE a.entity_id = y.parent_id
                                    AND y.address_type = \'billing\')')
        );
        $this->addFilterToMap(
            'products',
            new \Zend_Db_Expr('(SELECT GROUP_CONCAT(\' \', x.name)
                                FROM ' . $this->getTable('sales_order_item') . ' x
                                WHERE a.entity_id = x.order_id
                                    AND x.product_type != \'configurable\')')
        );
    }
    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        return $countSelect;
    }
     /**
     * Join reminder products info
     */
    public function joinOrderInfo()
    {
        $this->join( [ 'a' => $this->getTable('sales_order') ],
            'main_table.order_id = a.entity_id',
            [
                'store_id' => 'store_id',
                'increment_id' => 'increment_id'
            ]
        )
        ->addExpressionFieldToSelect(
            'customer_name',
            '(SELECT CONCAT(y.firstname, \' \', y.lastname)
                FROM ' . $this->getTable('sales_order_address') . ' y
                WHERE {{entity_id}} = y.parent_id
                    AND y.address_type = \'billing\')',
            [ 'entity_id' => 'a.entity_id' ]
        )
        ->addExpressionFieldToSelect(
            'products',
            '(SELECT GROUP_CONCAT(\' \', x.name)
                FROM ' . $this->getTable('sales_order_item') . ' x
                WHERE {{entity_id}} = x.order_id
                    AND x.product_type != \'configurable\')',
            [ 'entity_id' => 'a.entity_id' ]
        );
        return $this;
    }
}
