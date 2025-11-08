<?php
namespace Swissup\EasySlide\Model\ResourceModel\Slider;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\EasySlide\Model\Slider', 'Swissup\EasySlide\Model\ResourceModel\Slider');
    }
}
