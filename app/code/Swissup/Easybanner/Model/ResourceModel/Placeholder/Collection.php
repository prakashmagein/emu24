<?php
namespace Swissup\Easybanner\Model\ResourceModel\Placeholder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Easybanner\Model\Placeholder::class, \Swissup\Easybanner\Model\ResourceModel\Placeholder::class);
    }
}
