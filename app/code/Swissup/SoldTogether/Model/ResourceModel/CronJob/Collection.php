<?php

namespace Swissup\SoldTogether\Model\ResourceModel\CronJob;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\SoldTogether\Model\CronJob::class,
            \Swissup\SoldTogether\Model\ResourceModel\CronJob::class
        );
    }
}
