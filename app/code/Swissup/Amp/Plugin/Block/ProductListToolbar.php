<?php
namespace Swissup\Amp\Plugin\Block;

class ProductListToolbar
{
    const TEMPLATE = 'Magento_Catalog::product/list/toolbar/sorter.phtml';
    const AMP_TEMPLATE = 'Swissup_Amp::catalog/product/list/toolbar/sorter.phtml';

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get absolute path to template
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param string|null $template
     * @return string|bool
     */
    public function beforeGetTemplateFile(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $template = null
    ) {
        if ($this->helper->canUseAmp() && $template == self::TEMPLATE) {
            return self::AMP_TEMPLATE;
        }

        return $template;
    }
}
