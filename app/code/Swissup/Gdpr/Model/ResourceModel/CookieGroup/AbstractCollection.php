<?php

namespace Swissup\Gdpr\Model\ResourceModel\CookieGroup;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AbstractCollection extends \Magento\Framework\Data\Collection
{
    /**
     * @return $this
     */
    protected function _renderOrders()
    {
        $items = $this->getItems();

        uasort($items, function ($a, $b) {
            return $a->getSortOrder() > $b->getSortOrder() ? 1 : -1;
        });

        $this->_items = $items;

        return $this;
    }
}
