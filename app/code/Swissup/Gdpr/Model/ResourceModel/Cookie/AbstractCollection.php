<?php

namespace Swissup\Gdpr\Model\ResourceModel\Cookie;

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
            $aOrder = $a->getSortOrder();
            $bOrder = $b->getSortOrder();

            if ($aOrder !== null || $bOrder !== null) {
                $aOrder = $aOrder ?: 100000;
                $bOrder = $bOrder ?: 100000;

                return $aOrder > $bOrder ? 1 : -1;
            }

            return strnatcasecmp($a->getName(), $b->getName());
        });

        $this->_items = $items;

        return $this;
    }
}
