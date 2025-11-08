<?php

namespace Swissup\SoldTogether\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\DataObject\IdentityInterface;
use Swissup\SoldTogether\Model\Resolver\DataProvider\Products as DataProvider;
use Swissup\SoldTogether\Model\Config\Provider as ConfigProvider;

class Related extends AbstractProduct implements IdentityInterface
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Swissup\SoldTogether\Api\LinkedItemsProcessor
     */
    protected $itemsProcessor;

    /**
     * @var \Swissup\SoldTogether\Model\BlockState
     */
    protected $blockState;

    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var array|null
     */
    protected $itemsData = null;

    /**
     * @param ConfigProvider $configProvider
     * @param Context        $context
     * @param array          $data
     */
    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->itemsProcessor = $context->getItemsProcessor();
        $this->blockState = $context->getBlockState();
        $this->dataProvider = $context->getDataProvider();
        parent::__construct($context->getProductContext(), $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        // Set currentproduct ID to be able to get product IDs for cache key
        if ($this->getProduct()) {
            $this->dataProvider->setCurrentProductId($this->getProduct()->getId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        $this->fixNumberType($this->jsLayout);

        return parent::getJsLayout();
    }

    /**
     * Cast types for numbers
     */
    private function fixNumberType(&$data)
    {
        foreach ($data as &$v) {
            if (is_array($v)) {
                $this->fixNumberType($v);
            } elseif (is_numeric($v)) {
                settype($v, 'int');
            }
        }
    }

    /**
     * Get collection items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->itemsData === null) {
            $type = $this->configProvider->getLinkType();
            $this->dataProvider
                ->setPageSize($this->getProductsCount())
                ->setResourceType($type)
                ->setCanUseRandom($this->canUseRandom())
                ->setShowOutOfStock($this->showOutOfStock())
                ->setAllowedTypes($this->getAllowedProductTypes());

            try {
                $this->itemsData = $this->dataProvider->getData();
            } catch (\Exception $e) {
                $this->_logger->critical(
                    "{$this->getModuleName()} fetching collection error - {$e->getMessage()}"
                );
                $this->itemsData = [];
            }
        }

        return $this->itemsData['items'] ?? [];
    }

    /**
     * Get IDs of products
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        $isEnabled = $this->configProvider->isEnabled();
        $items = $this->getItems();
        if ($isEnabled && $items) {
            foreach ($items as $item) {
                $identities = array_merge($identities, $item['model']->getIdentities());
            }
        }

        return $identities;
    }

    /**
     * Get list of allowed product types to display
     *
     * @return array
     */
    public function getAllowedProductTypes()
    {
        if ($this->hasData('allowed_product_types')) {
            return $this->getData('allowed_product_types');
        }

        return $this->configProvider->getAllowedProductTypes();
    }

    /**
     * Show out of stock products also.
     *
     * @return boolean
     */
    public function showOutOfStock()
    {
        if ($this->hasData('show_out_of_stock')) {
            return !!$this->getData('show_out_of_stock');
        }

        return $this->configProvider->isShowOutOfStock();
    }

    /**
     * Check is random collection is allowed when product has no sold together relations.
     *
     * @return boolean
     */
    public function canUseRandom()
    {
        if ($this->hasData('can_use_random')) {
            return !!$this->getData('can_use_random');
        }

        return $this->configProvider->isRandom();
    }

    public function getProductsCount()
    {
        if ($this->hasData('products_count')) {
            return (int)$this->getData('products_count');
        }

        return $this->configProvider->getCount();
    }

    /**
     * Return HTML block with price for current product
     *
     * @return string
     */
    public function getCurrentProductPriceHtml()
    {
        if ($price = $this->getLayout()->getBlock('product.price.render.bundle.customization')) {
            // there is price renderer for bundle product price - use it
            return $price->toHtml();
        }

        return $this->getProductPrice($this->getProduct());
    }

    /**
     * Get modified product image HTML for SoldTogether item.
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  string  $imageId
     * @param  array   $attributes
     * @return string
     */
    public function getImageHtml($product, $imageId, $attributes = [])
    {
        $html = $this->getImage($product, $imageId, $attributes)->toHtml();

        return str_replace(
            "product-image-container-{$product->getId()}",
            "soldtogether-item-image-container-{$product->getId()}",
            $html
        );
    }

    /**
     * Render product details.
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function renderDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
        $html = $this->getProductDetailsHtml($product);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        $data = $product->getData('soldtogether_data') ?: [];
        if (!empty($data['promo_rule']) && !empty($data['promo_value'])) {
            $arguments['price_id_suffix'] = '-soldtogether-promo-' .
                $data['promo_rule'] .
                '-' .
                $data['promo_value'];
        }
        $html = parent::getProductPriceHtml(
            $product,
            $priceType,
            $renderZone,
            $arguments
        );

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->configProvider->isEnabled()) {
            $items = $this->getItems();
            $this->itemsProcessor->process($items);

            return parent::_toHtml();
        }

        return '';
    }
}
