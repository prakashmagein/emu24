<?php
namespace Emu24\CreditLimit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CreditReport extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('emu24_creditlimit_report', 'entity_id');
    }
}
