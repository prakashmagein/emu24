<?php

namespace Swissup\Highlight\Model\ResourceModel\Product\Popular;

class Collection extends \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\Collection implements \Swissup\Highlight\Model\ResourceModel\Product\AddPopularityFilterToCollectionInterface
{
    /**
     * Init Select
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinViews();
        return $this;
    }

    protected function joinViews()
    {
        $this->joinTable(
            ['idx_table' => $this->_getTableName()],
            'product_id=entity_id',
            [
                'product_id'    => 'product_id',
                'item_store_id' => 'store_id',
                'added_at'      => 'idx_table.added_at',
                'popularity'    => 'COUNT(e.entity_id)'
            ]
        );

        // group views of different users
        $this->getSelect()->group('e.entity_id');

        return $this;
    }

    /**
     * @param $min
     * @param false $max
     * @return $this
     */
    public function addPopularityFilter($min, $max = false)
    {
        if ($min && $max) {
            $this->addPopularityFilter($min)->addPopularityFilter(null, $max);
        } elseif ($min) {
            $this->getSelect()->having('COUNT(e.entity_id) >= ?', $min);
        } elseif ($max) {
            $this->getSelect()->having('COUNT(e.entity_id) <= ?', $max);
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
            if (stripos((string) $sql, 'group by') !== false) {
                $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
            } else {
                $this->_totalRecords = parent::getSize();
            }
        }
        return intval($this->_totalRecords);
    }

    /**
     * @param $period
     * @throws \Exception
     */
    public function addPeriodFilter($period)
    {
        // locale date is not used, because save does not use it too.
        // @see /Magento/Reports/Model/Product/Index/AbstractIndex::beforeSave
        $dateFrom = (new \DateTime())
            ->sub(new \DateInterval($period))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $this->getSelect()->where('added_at > ?', $dateFrom);

        return $this;
    }
}
