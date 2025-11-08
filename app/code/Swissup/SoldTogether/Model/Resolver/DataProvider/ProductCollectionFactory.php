<?php

namespace Swissup\SoldTogether\Model\Resolver\DataProvider;

use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\SoldTogether\Helper\Stock as HelperStock;

class ProductCollectionFactory
{
    private $catalogConfig;
    private $collectionFactory;
    private $helperStock;
    private $storeManager;
    private $visibility;

    public function __construct(
        CatalogConfig $catalogConfig,
        CollectionFactory $collectionFactory,
        HelperStock $helperStock,
        StoreManagerInterface $storeManager,
        Visibility $visibility
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->collectionFactory = $collectionFactory;
        $this->helperStock = $helperStock;
        $this->storeManager = $storeManager;
        $this->visibility = $visibility;
    }

    public function create(
        array $allowedTypes = [],
        bool $showOutOfStock = true
    ) {
        $storeId = $this->storeManager->getStore()->getId();
        $visibleInCatalogIds = $this->visibility->getVisibleInCatalogIds();
        $isCheckoutEnabled = $this->helperStock->isModuleOutputEnabled('Magento_Checkout');

        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('required_options')
            ->addStoreFilter($storeId)
            ->setVisibility($visibleInCatalogIds);

        if ($isCheckoutEnabled) {
            $collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addUrlRewrite();
        }

        if ($allowedTypes) {
            $collection->addAttributeToFilter('type_id', ['in' => $allowedTypes]);
        }

        if (!$showOutOfStock) {
            $this->helperStock->addInStockFilterToCollection($collection);
        }

        return $collection;
    }
}
