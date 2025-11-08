<?php

namespace Swissup\Highlight\Model\ResourceModel\Product\Bulksellers;

class Collection extends \Swissup\Highlight\Model\ResourceModel\Product\Bestsellers\Collection
{
    protected function getPopularityQuery()
    {
        return new \Zend_Db_Expr('SUM(order_items.qty_ordered) - SUM(order_items.qty_canceled)');
    }
}
