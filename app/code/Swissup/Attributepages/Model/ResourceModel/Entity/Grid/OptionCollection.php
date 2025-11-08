<?php
namespace Swissup\Attributepages\Model\ResourceModel\Entity\Grid;

class OptionCollection extends AbstractGridCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addOptionOnlyFilter();
    }
}
