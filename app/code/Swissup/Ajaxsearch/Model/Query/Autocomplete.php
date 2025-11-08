<?php

namespace Swissup\Ajaxsearch\Model\Query;

/**
 * Search query model
 */
class Autocomplete extends \Swissup\Ajaxsearch\Model\Query
{
    /**
     * Retrieve collection of suggest queries
     *
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     */
    protected function _getSuggestCollection()
    {
        return $this->_queryCollectionFactory
            ->setInstanceName(\Magento\Search\Model\ResourceModel\Query\Collection::class)
            ->create()
            ->setStoreId($this->getStoreId())
            ->setQueryFilter($this->getQueryText());
    }
}
