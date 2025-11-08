<?php

namespace Swissup\Gdpr\Model\ResourceModel\Traits;

trait MergedCollection
{
    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_setIsLoaded(true);

        foreach ($this->collections as $collection) {
            foreach ($collection as $item) {
                if ($custom = $this->getItemByColumnValue($this->mergeKey, $item->getData($this->mergeKey))) {
                    $custom->setData(array_merge($item->getData(), $custom->getData()));
                    continue;
                }
                $this->addItem($item);
            }
        }

        $this->_renderFilters();
        $this->_renderOrders();

        return $this;
    }

    public function setStoreId($storeId)
    {
        foreach ($this->collections as $collection) {
            if (!method_exists($collection, 'setStoreId')) {
                continue;
            }
            $collection->setStoreId($storeId);
        }
        return $this;
    }
}
