<?php

namespace Swissup\Highlight\Block\ProductList;

class NewList extends Attribute\Date
{
    const PAGE_TYPE = 'new';

    protected $widgetPageVarName = 'hnp';

    protected $widgetPriceSuffix = 'new';

    protected $widgetCssClass = 'highlight-new';

    public function getAttributeCode()
    {
        return 'news_from_date,news_to_date';
    }
}
