<?php

namespace Swissup\Gdpr\Model\ResourceModel\Client;

abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'entity_id' => 'main_table.entity_id',
        ]
    ];

    protected $customerNameExpr;

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->customerNameExpr = $this->getConnection()->getConcatSql(
            [
                'customer_entity.firstname',
                'customer_entity.lastname',
            ],
            ' '
        );
        $this->addFilterToMap('customer_name', $this->customerNameExpr);

        parent::_initSelect();
    }

    /**
     * Add customer name expression to the collection
     */
    public function addCustomerNameToSelect()
    {
        $this->getSelect()->joinLeft(
            ['customer_entity' => $this->getTable('customer_entity')],
            'customer_id = customer_entity.entity_id',
            ['customer_name' => $this->customerNameExpr]
        );

        return $this;
    }
}
