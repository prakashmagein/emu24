<?php

namespace Swissup\SoldTogether\Model;

class CronJob extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SoldTogether\Model\ResourceModel\CronJob::class);
    }
}
