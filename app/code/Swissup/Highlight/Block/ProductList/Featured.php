<?php

namespace Swissup\Highlight\Block\ProductList;

class Featured extends Attribute\Yesno
{
    const PAGE_TYPE = 'featured';

    protected $widgetPageVarName = 'hfp';

    protected $widgetPriceSuffix = 'featured';

    protected $widgetCssClass = 'highlight-featured';

    public function getAttributeCode()
    {
        return 'featured';
    }
}
