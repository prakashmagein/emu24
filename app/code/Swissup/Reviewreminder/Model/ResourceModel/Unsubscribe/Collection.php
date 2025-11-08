<?php
namespace Swissup\Reviewreminder\Model\ResourceModel\Unsubscribe;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\Reviewreminder\Model\Unsubscribe::class,
            \Swissup\Reviewreminder\Model\ResourceModel\Unsubscribe::class
        );
    }
}
