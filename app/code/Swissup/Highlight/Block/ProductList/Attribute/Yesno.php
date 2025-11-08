<?php

namespace Swissup\Highlight\Block\ProductList\Attribute;

class Yesno extends \Swissup\Highlight\Block\ProductList\All
{
    protected $widgetPageVarName = 'hynp';

    protected $widgetPriceSuffix = 'yesno';

    protected $widgetCssClass = 'highlight-yesno';

    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function prepareProductCollection($collection)
    {
        parent::prepareProductCollection($collection);
        try {
            $attributeCode = $this->getAttributeCode();
            $collection->addAttributeToFilter($attributeCode, 1);
        } catch (\Exception $e) {
            $this->setTemplate(null);
            $this->setCustomTemplate(null);
        }
    }
}
