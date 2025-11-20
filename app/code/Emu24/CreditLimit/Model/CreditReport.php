<?php
namespace Emu24\CreditLimit\Model;

use Magento\Framework\Model\AbstractModel;

class CreditReport extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Emu24\CreditLimit\Model\ResourceModel\CreditReport::class);
    }
}
