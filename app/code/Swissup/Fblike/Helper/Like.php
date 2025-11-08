<?php

namespace Swissup\Fblike\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Like extends AbstractHelper
{

    /**
     * Layout
     *
     * @var \Magento\Framework\View\Layout
     */
    private $layout;

    /**
     * Like button renderer
     *
     * @var \Swissup\Fblike\Block\Button
     */
    private $renderer;

    /**
     * @param \Magento\Framework\View\Layout        $layout
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->layout = $layout;
        parent::__construct($context);
    }

    /**
     * Get like FB Like Button html
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductLike(\Magento\Catalog\Model\Product $product)
    {
        return $this->renderButton($product, 'category');
    }

    /**
     * Render FB Like Button
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  string                         $section
     * @return string
     */
    public function renderButton(
        \Magento\Catalog\Model\Product $product,
        $section
    ) {
        return $this->_getRenderer()
            ->setProduct($product)
            ->setConfigSection($section)
            ->toHtml();
    }

    /**
     * Get like button renderer
     *
     * @return \Swissup\Fblike\Block\Button
     */
    private function _getRenderer()
    {
        if (!isset($this->renderer)) {
            $this->renderer = $this->layout
                ->createBlock('\Swissup\Fblike\Block\Button')
                ->setTemplate('button.phtml');
        }

        return $this->renderer;
    }

    /**
     * Get Facebook App Id
     *
     * @return string|null
     */
    public function getAppId()
    {
        return $appId = $this->scopeConfig->getValue(
            'fblike/general/app',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
