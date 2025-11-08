<?php

namespace Swissup\Navigationpro\Block\Common\Catalog\Compare;

class Sidebar extends \Magento\Framework\View\Element\Template
{
    protected $jsLayout = [
        'components' => [
            'compareProducts' => [
                'component' => 'Magento_Catalog/js/view/compare-products',
            ],
        ],
    ];

    public function getTemplate()
    {
        $template = parent::getTemplate();
        if (!$template) {
            $template = 'Magento_Catalog::product/compare/sidebar.phtml';
        }
        return $template;
    }
}
