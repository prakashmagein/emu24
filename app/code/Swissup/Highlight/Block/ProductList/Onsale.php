<?php

namespace Swissup\Highlight\Block\ProductList;

class Onsale extends Attribute\Date
{
    const PAGE_TYPE = 'onsale';

    protected $widgetPageVarName = 'hsp';

    protected $widgetPriceSuffix = 'onsale';

    protected $widgetCssClass = 'highlight-onsale';

    public function getAttributeCode()
    {
        return 'special_from_date,special_to_date';
    }

    public function prepareProductCollection($collection)
    {
        parent::prepareProductCollection($collection);
        $collection->addAttributeToFilter('special_price', array('gt' => 0));
    }
}
