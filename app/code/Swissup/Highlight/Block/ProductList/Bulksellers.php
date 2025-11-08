<?php

namespace Swissup\Highlight\Block\ProductList;

class Bulksellers extends Popular
{
    const PAGE_TYPE = 'bulksellers';

    protected $widgetPageVarName = 'hbulkp';

    protected $widgetPriceSuffix = 'bulksellers';

    protected $widgetCssClass = 'highlight-bulksellers';

    public function getProductCollectionType()
    {
        return \Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory::TYPE_BULKSELLERS;
    }
}
