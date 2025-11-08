<?php
namespace Swissup\Ajaxsearch\Model\Query\Autocomplete;

/**
 * Search query model
 */
class Popular extends \Swissup\Ajaxsearch\Model\Query
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
            ->setPopularQueryFilter($this->getStoreId());
            ;
    }
}
