<?php

namespace Swissup\EasySlide\Model\ResourceModel\Slides;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'slide_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\EasySlide\Model\Slides::class,
            \Swissup\EasySlide\Model\ResourceModel\Slides::class
        );
    }
}
