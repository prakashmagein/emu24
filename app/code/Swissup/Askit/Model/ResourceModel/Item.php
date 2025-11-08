<?php
namespace Swissup\Askit\Model\ResourceModel;

/**
 * Askit Item mysql resource
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_askit_item', 'id');
    }
}
