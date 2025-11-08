<?php

namespace Swissup\Highlight\Model\ResourceModel\Product\Bestsellers;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection implements \Swissup\Highlight\Model\ResourceModel\Product\AddPopularityFilterToCollectionInterface
{

    private array $bestsellerFilters = [];
    private bool $isBestsellerFiltersRendered = false;

    /**
     * Init Select
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinOrderedQty();
        return $this;
    }

    protected function getPopularityQuery()
    {
        return new \Zend_Db_Expr('COUNT(order_items.qty_ordered) - SUM(CASE WHEN order_items.qty_canceled > 0 THEN 1 ELSE 0 END)');
    }

    protected function joinOrderedQty()
    {
        // $connection = $this->getConnection();
        // $orderJoinCondition = [
        //     'sales_order.entity_id = order_items.order_id',
        //     $connection->quoteInto("sales_order.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        // ];

        // $this->getSelect()
        //     ->join(
        //         ['order_items' => $this->getTable('sales_order_item')],
        //         'order_items.product_id=e.entity_id',
        //         [
        //             'popularity' => $this->getPopularityQuery(),
        //             'order_items_name' => 'order_items.name'
        //         ]
        //     )->join(
        //         ['sales_order' => $this->getTable('sales_order')],
        //         implode(' AND ', $orderJoinCondition),
        //         []
        //     );

        // $this->getSelect()
        //     ->where('parent_item_id IS NULL')
        //     ->group('e.entity_id');

        return $this;
    }

    /**
     * @param $min
     * @param null $max
     * @return $this
     */
    public function addPopularityFilter($min, $max = false)
    {
        if ($min && $max) {
            $this->addPopularityFilter($min)->addPopularityFilter(null, $max);
        } elseif ($min) {
            // $this->getSelect()->having('COUNT(order_items.qty_ordered) >= ?', $min);
            $this->bestsellerFilters['popularity_min'] = $min;
        } elseif ($max) {
            // $this->getSelect()->having('COUNT(order_items.qty_ordered) <= ?', $max);
            $this->bestsellerFilters['popularity_max'] = $max;
        }

        return $this;
    }

    /**
     * Fixed size calculation, when group by is used
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelectCountSql();
            $part = $sql->getPart(\Magento\Framework\DB\Select::GROUP);
            if (!is_array($part) || !count($part)) {
                // This GROUP BY is required to get correct numbers in Magento 2.4.x
                // Bue to method joinOrderedQty
                $sql->group('e.entity_id');
            }

            $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }

    /**
     * @param $period
     * @throws \Exception
     */
    public function addPeriodFilter($period)
    {
        $dateFrom = (new \DateTime())
            ->sub(new \DateInterval($period))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        // $this->getSelect()->where('sales_order.created_at > ?', $dateFrom);
        $this->bestsellerFilters['created_at'] =  $dateFrom;

        return $this;
    }

    protected function _renderFilters()
    {
        parent::_renderFilters();

        if (!$this->isBestsellerFiltersRendered
            && $this->bestsellerFilters
        ) {
            $bestsellersTableTmp = $this->createTemporaryTable($this->bestsellerFilters);
            $this->getSelect()
                ->join(
                    ['bestseller' => $bestsellersTableTmp],
                    'bestseller.product_id=e.entity_id',
                    [
                        'popularity' => 'bestseller.popularity',
                    ]
                );

            $this->isBestsellerFiltersRendered = true;
        }

        return $this;
    }

    private function createTemporaryTable(array $filters): string
    {
        $connection = $this->getConnection();
        $orderItemsSelect = $connection->select()
            ->from(
                ['order_items' => $this->getTable('sales_order_item')],
                [
                    'product_id' => 'order_items.product_id',
                    'popularity' => $this->getPopularityQuery()
                ]
            )
            ->where('order_items.parent_item_id IS NULL OR order_items.parent_item_id = 0')
            ->group(['product_id']);

        array_walk(
            $filters,
            function ($value, $filter, $select) {
                switch ($filter) {
                    case 'created_at':
                        $select->where('order_items.created_at > ?', $value);
                        break;

                    case 'popularity_min':
                        $select->having('popularity >= ?', $value);
                        break;

                    case 'popularity_max':
                        $select->having('popularity <= ?', $value);
                        break;
                }
            },
            $orderItemsSelect
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        return $objectManager
            ->get(\Magento\Framework\DB\TemporaryTableService::class)
            ->createFromSelect(
                $orderItemsSelect,
                $connection,
                [
                    'PRIMARY' => ['product_id']
                ]
            );
    }
}
