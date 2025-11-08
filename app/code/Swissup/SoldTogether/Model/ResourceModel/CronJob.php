<?php

namespace Swissup\SoldTogether\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CronJob extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['data' => [null, []]];

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_soldtogether_cron_job', 'id');
    }
}
