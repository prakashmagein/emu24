<?php

namespace Swissup\Attributepages\Model\PageRule;

class ProductCondition extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    protected $_defaultOperatorInputByType = [
        'text' => ['()', '=='],
        'select' => ['()', '=='],
        'multiselect' => ['()', '=='],
        'category' => ['=='],
        'boolean' => ['=='],
    ];

    public function getInputType()
    {
        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids') {
            return 'category';
        }

        if ($this->getAttributeObject()->getFrontendInput() === 'boolean') {
            return 'boolean';
        }

        return 'multiselect';
    }

    public function getValueElementType()
    {
        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids') {
            return 'text';
        }

        if ($this->getAttributeObject()->getFrontendInput() === 'boolean') {
            return 'select';
        }

        return 'multiselect';
    }
}
