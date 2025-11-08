<?php
namespace Swissup\Reviewreminder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Unsubscribe extends AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_reviewreminder_unsubscribe', 'id');
    }
}
