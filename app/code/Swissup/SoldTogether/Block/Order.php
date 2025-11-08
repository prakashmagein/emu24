<?php

namespace Swissup\SoldTogether\Block;

use Swissup\SoldTogether\Model\Config\Source\Layout as LayoutStyle;
use Swissup\SoldTogether\Model\Config\Provider as ConfigProvider;

class Order extends Related
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\App\Http\Context       $httpContext
     * @param ConfigProvider                            $configProvider
     * @param Context                                   $context
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        parent::__construct($configProvider, $context, $data);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => 86400,
                'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        // Check if layout still has this block. Otherwise CRITICAL message in log.
        // Maybe it is unset from layout programatically.
        // For example, easytabs in Argento Force
        $layout = $this->getLayout();
        if ($layout->hasElement($this->getNameInLayout())) {
            // Add details renderer list
            $rendererList = $this->addChild(
                'details.renderers',
                \Magento\Framework\View\Element\RendererList::class
            );
            // Add default renderer
            $renderer = $rendererList->addChild(
                'default',
                \Magento\Framework\View\Element\Text::class
            );
            // Add configurable product details renderer
            $renderer = $rendererList->addChild(
                'configurable',
                Product\Renderer\Configurable::class
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'SOLDTOGETHER_' . $this->configProvider->getLinkType(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getProductsCount(),
            implode(',', $this->dataProvider->getProductIds()),
        ];
    }

    /**
     * Get tax display config
     *
     * @return string
     */
    public function getTaxDisplayConfig()
    {
        return $this->configProvider->getTaxDisplay();
    }

    /**
     * Get layout style for block
     *
     * @return string
     */
    public function getLayoutStyle()
    {
        if ($this->hasData('layout_style')) {
            return $this->getData('layout_style');
        }

        return $this->configProvider->getLayout();
    }

    /**
     * Check if block presention is 'Amazon'
     *
     * @return boolean
     */
    public function isLayoutStyleDefault()
    {
        return $this->getLayoutStyle() === LayoutStyle::AMAZON_DEFAULT;
    }

    /**
     * Check if block presention is 'Stripe'
     *
     * @return boolean
     */
    public function isLayoutStyleStripe()
    {
        return $this->getLayoutStyle() === LayoutStyle::AMAZON_STRIPE;
    }

    public function renderCurrentItem(
        \Magento\Framework\DataObject $item,
        $template = 'product/order/current-item.phtml'
    ) {
        $fileName = $this->getTemplateFile($template);

        return $this->assign('item', $item)->fetchView($fileName);
    }

    /**
     * Render related item
     *
     * @param  \Magento\Framework\DataObject $product
     * @return string
     */
    public function renderRelatedItem(
        \Magento\Framework\DataObject $item,
        $template = 'product/order/related-item.phtml'
    ) {
        $stateName = 'rendering_soldtogether_item_' . $this->configProvider->getLinkType();
        $this->blockState->set($stateName);
        $fileName = $this->getTemplateFile($template);
        $html = $this->assign('item', $item)->fetchView($fileName);
        $this->blockState->set(null);

        return $html;
    }
}
