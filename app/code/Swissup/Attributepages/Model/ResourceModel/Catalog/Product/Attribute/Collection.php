<?php
namespace Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
{
    public function toOptionArray()
    {
        return $this->_toOptionArray('attribute_id', 'frontend_label');
    }
    public function toOptionHash()
    {
        return $this->_toOptionHash('attribute_id', 'frontend_label');
    }
}
