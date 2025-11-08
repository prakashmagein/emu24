<?php
namespace Swissup\Attributepages\Model\ResourceModel\Entity\Grid;

class PageCollection extends AbstractGridCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addAttributeOnlyFilter();
    }
}
