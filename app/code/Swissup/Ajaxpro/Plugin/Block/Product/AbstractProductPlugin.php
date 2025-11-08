<?php
namespace Swissup\Ajaxpro\Plugin\Block\Product;

use Magento\Framework\Exception\NoSuchEntityException;

class AbstractProductPlugin
{
    /**
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * Constructor
     *
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Swissup\Ajaxpro\Helper\Config $configHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->configHelper = $configHelper;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param \Magento\Catalog\Block\Product\AbstractProduct $subject
     * @param callable $proceed
     * @param $product
     * @param array $additional
     * @return string
     */
    public function aroundGetAddToCartUrl(
        \Magento\Catalog\Block\Product\AbstractProduct $subject,
        callable $proceed,
        $product,
        $additional = []
    ) {
        if (!$subject instanceof \Magento\Catalog\Block\Product\ListProduct) {
            return $proceed($product, $additional);
        }

        $isOverride = $this->configHelper->isOverrideGetAddToCartUrl();
        if (!$isOverride) {
            return $proceed($product, $additional);
        }
//        $product = $this->getProduct($subject->getRequest());
        if ($product && $this->isProductHasOptions($product)) {

            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $subject->getProductUrl($product, $additional);
        }

        return $proceed($product, $additional);
    }

    /**
     *
     * @param  \Magento\Catalog\Model\Product  $product
     * @return boolean
     */
    private function isProductHasOptions(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getTypeID() === 'grouped') {
            return true;
        }
        $typeInstance = $product->getTypeInstance();
        $hasOptions = $typeInstance->hasOptions($product);
        if ($hasOptions === null) {
            // Value can be NULL when flat catalog is enabled.
            // Read value directly from DB.
            $hasOptions = $this->readHasOptions($product);
        }

        return $typeInstance && ($typeInstance->hasRequiredOptions($product) || $hasOptions);
    }

    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    private function readHasOptions(\Magento\Catalog\Model\Product $product)
    {
        $collection = $this->getProductCollection();
        $collection->addFieldToFilter(
            $collection->getIdFieldName(),
            $product->getId()
        );
        $collection->addFieldToSelect('has_options');
        $item = $collection->fetchItem();

        return $item ? $item->getData('has_options') : false;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection()
    {
      return $this->productCollectionFactory->create();
    }
}
