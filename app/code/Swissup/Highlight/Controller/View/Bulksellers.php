<?php

namespace Swissup\Highlight\Controller\View;

class Bulksellers extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        return $this->_objectManager
            ->get('Swissup\Highlight\Helper\Page')
            ->preparePage(
                $this,
                \Swissup\Highlight\Block\ProductList\Bulksellers::PAGE_TYPE
            );
    }
}
