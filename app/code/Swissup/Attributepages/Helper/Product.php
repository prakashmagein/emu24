<?php
namespace Swissup\Attributepages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Product extends AbstractHelper
{
    private $_data;
    /**
     * Array of helper variables
     *
     * @var array
     */
    protected $data = [];
    /**
     * @var \Swissup\Attributepages\Block\Product\Option
     */
    protected $block = null;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attrCollectionFactory;
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    protected $attrPagesCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrPagesCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollectionFactory,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrPagesCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        parent::__construct($context);
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->attrPagesCollectionFactory = $attrPagesCollectionFactory;
        $this->storeManager = $storeManager;
        $this->layout = $layout;
    }

    public function toHtml()
    {
        if (!$this->getBlock() || !$this->getBlock()->getProduct()) {
            return '';
        }
        $collection = $this->getData('collection');
        if ($this->getData('attribute_code')) {
            $this->appendPages(
                $collection ? $collection : $this->getBlock()->getProduct(),
                $this->getData('attribute_code'),
                $this->getData('parent_page_identifier')
            );
        }
        $output = $this->getBlock()->toHtml();
        // fix to reset configuration in case if block is rendered in
        // another template with different config
        $this->block = null;
        return $output;
    }
    /**
     * @return \Swissup\Attributepages\Block\Product\Option
     */
    public function getBlock()
    {
        if (null === $this->block) {
            $this->block = $this->layout
                ->createBlock('Swissup\Attributepages\Block\Product\Option')
                ->setTemplate('Swissup_Attributepages::product/options.phtml');
        }
        return $this->block;
    }
    /**
     * Magic method to call Swissup\Attributepages\Block\Product\Option methods
     *
     * @param  string $name
     * @param  array $arguments
     * @return Swissup\Attributepages\Helper\Product
     */
    public function __call($name, $arguments)
    {
        call_user_func_array([$this->getBlock(), $name], $arguments);
        return $this;
    }
    /**
     * Set image width and height
     *
     * @param int $width
     * @param int $height
     */
    public function setSize($width, $height)
    {
        $this->getBlock()->setWidth($width)->setHeight($height);
        return $this;
    }
    /**
     * Set collection to load attributepages for each of the collection items
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function setCollection($collection)
    {
        return $this->setData('collection', $collection);
    }
    /**
     * Set attribute code to load and show
     *
     * @param mixed $code String of array
     */
    public function setAttributeCode($code)
    {
        $this->getBlock()->setAttributeToShow($code);
        return $this->setData('attribute_code', $code);
    }
    /**
     * Set parent page identifier. Usefull when attributepage options belongs
     * to multiple attributepages
     *
     * @param mixed $identifier key=>value array or string
     */
    public function setParentPageIdentifier($identifier)
    {
        return $this->setData('parent_page_identifier', $identifier);
    }
    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }
    public function getData($key, $default = null)
    {
        if (!array_key_exists($key, $this->_data)) {
            return $default;
        }
        return $this->_data[$key];
    }
    /**
     * Find parent page among $collection for $option
     *
     * @param  \Swissup\Attributepages\Model\Entity $option
     * @param  \Swissup\Attributepages\Model\ResourceModel\Entity\Collection $collection
     * @param  int $storeId
     * @param  string $identifier
     * @return \Swissup\Attributepages\Model\Entity|false
     */
    public function findParentPage(
        \Swissup\Attributepages\Model\Entity $option,
        \Swissup\Attributepages\Model\ResourceModel\Entity\Collection $collection,
        $storeId,
        $identifier = null
    ) {
        if ($identifier) {
            return $collection->getItemByColumnValue('identifier', $identifier);
        }

        $parentPage = false;
        $parentPages = $collection->getItemsByColumnValue('option_id', null);
        foreach ($parentPages as $page) {
            if ($page->getAttributeId() !== $option->getAttributeId()) {
                continue;
            }

            $excludedOptions = $page->getExcludedOptionIdsArray();
            if (in_array($option->getOptionId(), $excludedOptions)) {
                continue;
            }

            if ($parentPage) {
                $storeIds = $page->getStoreId();
                if (!is_array($storeIds)) {
                    $storeIds = [$storeIds];
                }

                if (!in_array($storeId, $storeIds)) {
                    continue;
                }
            }

            $parentPage = $page;
            $storeIds = $parentPage->getStoreId();
            if (!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }

            if (in_array($storeId, $storeIds)) {
                break;
            }
        }

        return $parentPage;
    }
    /**
     * Append attributepages to product collection or single product
     *
     * @param  mixed $collection                Product collection or product itself
     * @param  mixed $attributes                Attribute code or array of codes
     * @param  mixed $parentPageIdentifiers     Attributepage identifier. Optional parameter.
     * @return Swissup\Attributepages\Helper\Product
     */
    public function appendPages($collection, $attributes, $parentPageIdentifiers = null)
    {
        if (!$attributes) {
            return $this;
        }

        if ($collection instanceof \Magento\Catalog\Model\Product) {
            $product = $collection;
            $collection = [$collection];
        } else {
            $product = $collection->getFirstItem();
        }

        if (!is_array($attributes)) {
            if ($parentPageIdentifiers && !is_array($parentPageIdentifiers)) {
                $parentPageIdentifiers = [
                    $attributes => $parentPageIdentifiers
                ];
            }
            $attributes = [$attributes];
        }

        // do not load already loaded collection
        $loaded = $product->getAttributepages();
        if (null !== $loaded) {
            $notLoaded = array_diff($attributes, array_keys($loaded));
            if (!$notLoaded) {
                return $this;
            }
        }

        $attributeCollection = $this->attrCollectionFactory->create()
            ->addFieldToFilter('attribute_code', ['IN' => $attributes]);

        if (!$attributeCollection->count()) {
            return $this;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $attributepageCollection = $this->attrPagesCollectionFactory->create()
            ->addUseForAttributePageFilter() // enabled flag
            ->addFieldToFilter('attribute_id', [
                'IN' => $attributeCollection->getColumnValues('attribute_id')
            ])
            ->addStoreFilter($storeId);

        foreach ($attributepageCollection as $attributepage) {
            $item = $attributeCollection->getItemById($attributepage->getAttributeId());
            if (!$item) {
                continue;
            }
            $attributepage->setAttributeCode($item->getAttributeCode());
        }

        // prepare each of the possible options
        $result = [];
        foreach ($collection as $product) {
            foreach ($attributes as $attributeCode) {
                $optionIds = (string) $product->getData($attributeCode);
                $optionIdsArr = explode(',', $optionIds);
                foreach ($optionIdsArr as $optionId) {
                    if (!$optionId || !empty($result[$attributeCode][$optionId])) {
                        continue;
                    }

                    if (!$option = $this->findOption($attributepageCollection, $optionId, $storeId)) {
                        continue;
                    }

                    if (is_array($parentPageIdentifiers)) {
                        if (isset($parentPageIdentifiers[$attributeCode])) {
                            $parentPageIdentifier = $parentPageIdentifiers[$attributeCode];
                        } else {
                            $parentPageIdentifier = null;
                        }
                    } else {
                        $parentPageIdentifier = $parentPageIdentifiers;
                    }

                    $option->setParentPageIdentifier($parentPageIdentifier);
                    $parentPage = $this->findParentPage(
                        $option,
                        $attributepageCollection,
                        $storeId,
                        $parentPageIdentifier
                    );

                    if (!$parentPage) { // disabled page
                        continue;
                    }

                    $option->setParentPage($parentPage);
                    $result[$attributeCode][$optionId] = $option;
                }
            }
        }

        foreach ($collection as $product) {
            $attributepages = [];
            foreach ($attributes as $attributeCode) {
                $optionIds = (string) $product->getData($attributeCode);
                $optionIdsArr = explode(',', $optionIds);
                foreach ($optionIdsArr as $optionId) {
                    if (!$optionId || empty($result[$attributeCode][$optionId])) {
                        $attributepages[$attributeCode] = null;
                        continue;
                    }
                    $attributepages[$attributeCode][] = $result[$attributeCode][$optionId];
                }
            }
            $product->setAttributepages($attributepages);
        }
        return $this;
    }
    /**
     * Find the most suitable option page from $collection
     *
     * @param  \Swissup\Attributepages\Model\ResourceModel\Entity\Collection $collection
     * @param  int $storeId
     * @return \Swissup\Attributepages\Model\Entity or false
     */
    public function findOption(
        \Swissup\Attributepages\Model\ResourceModel\Entity\Collection $collection,
        $optionId,
        $storeId)
    {
        $option = false;
        foreach ($collection as $possibleOption) {
            if (!$possibleOption->getOptionId()) { // attribute based page
                continue;
            }
            if ($possibleOption->getOptionId() !== $optionId) {
                continue;
            }
            if ($option) {
                if (!in_array($storeId, $possibleOption->getStores())) {
                    continue;
                }
            }
            $option = $possibleOption;
            if (in_array($storeId, $option->getStores())) {
                break;
            }
        }
        return $option;
    }
}
