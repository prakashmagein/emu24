<?php

namespace Swissup\Highlight\Controller\View;

class Bestsellers extends \Magento\Framework\App\Action\Action
{
    /**
     * Show products
     *
     * @return void
     */
    public function execute()
    {
        return $this->_objectManager
            ->get('Swissup\Highlight\Helper\Page')
            ->preparePage(
                $this,
                \Swissup\Highlight\Block\ProductList\Bestsellers::PAGE_TYPE
            );
    }
}
