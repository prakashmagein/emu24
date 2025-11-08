<?php

namespace Swissup\Highlight\Block\ProductList;

class Bestsellers extends Popular
{
    const PAGE_TYPE = 'bestsellers';

    protected $widgetPageVarName = 'hbp';

    protected $widgetPriceSuffix = 'bestsellers';

    protected $widgetCssClass = 'highlight-bestsellers';

    public function getProductCollectionType()
    {
        return \Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory::TYPE_BESTSELLERS;
    }
}
