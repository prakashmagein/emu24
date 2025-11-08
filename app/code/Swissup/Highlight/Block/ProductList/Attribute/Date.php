<?php

namespace Swissup\Highlight\Block\ProductList\Attribute;

class Date extends \Swissup\Highlight\Block\ProductList\All
{
    protected $widgetPageVarName = 'hdp';

    protected $widgetPriceSuffix = 'date';

    protected $widgetCssClass = 'highlight-date';

    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function prepareProductCollection($collection)
    {
        parent::prepareProductCollection($collection);
        try {
            $attribute = $this->getAttributeCode();
            if (!is_array($attribute)) {
                $attribute = explode(',', $attribute);
            }

            $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

            $fromAttribute = isset($attribute[0]) && !empty($attribute[0]) ? $attribute[0] : false;
            $toAttribute = isset($attribute[1]) && !empty($attribute[1]) ? $attribute[1] : false;

            $collection->addDateRangeFilter($fromAttribute, $toAttribute, $todayStartOfDayDate, $todayEndOfDayDate);
        } catch (\Exception $e) {
            $this->setTemplate(null);
            $this->setCustomTemplate(null);
        }
    }

    public function getDefaultSortField()
    {
        $attribute = $this->getAttributeCode();
        if (!is_array($attribute)) {
            $attribute = explode(',', $attribute);
        }

        if (!empty($attribute[0])) {
            return $attribute[0];
        } elseif (!empty($attribute[1])) {
            return $attribute[1];
        }
        return parent::getDefaultSortField();
    }

    public function getDefaultSortFieldLabel()
    {
        return __('Date');
    }

    public function getDefaultSortDirection()
    {
        return 'desc';
    }
}
