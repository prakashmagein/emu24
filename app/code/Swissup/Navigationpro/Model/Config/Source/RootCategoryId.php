<?php

namespace Swissup\Navigationpro\Model\Config\Source;

class RootCategoryId extends \Magento\Catalog\Model\Config\Source\Category
{
    public function toOptionArray($addEmpty = true)
    {
        return parent::toOptionArray(false);
    }
}
