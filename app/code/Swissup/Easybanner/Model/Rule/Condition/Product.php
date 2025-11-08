<?php

namespace Swissup\Easybanner\Model\Rule\Condition;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * Set attribute value
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _setAttributeValue(\Magento\Framework\Model\AbstractModel $model)
    {
        $value = $model->getData($this->getAttribute());

        // @see parent::_setAttributeValue
        if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : [];
        } elseif ($this->getAttributeObject()->getBackendType() == 'datetime') {
            $value = strtotime($value);
        }

        $model->setData($this->getAttribute(), $value);

        return $this;
    }
}
