<?php
namespace Emu24\CreditLimit\Model\ResourceModel\CreditReport;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Emu24\CreditLimit\Model\CreditReport::class, \Emu24\CreditLimit\Model\ResourceModel\CreditReport::class);
    }
}
