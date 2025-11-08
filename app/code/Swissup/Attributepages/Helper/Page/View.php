<?php

namespace Swissup\Attributepages\Helper\Page;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\Store;
use Swissup\Attributepages\Model\Entity as AttributepagesModel;

class View extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    protected $attrpagesCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Swissup\Attributepages\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Swissup\Attributepages\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Swissup\Attributepages\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->attrpagesCollectionFactory = $attrpagesCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->categoryFactory = $categoryFactory;
        $this->helper = $helper;
    }
    /**
     * @param  mixed $pageId       Current page identifier or id
     * @param  mixed $parentPageId Parent page identifier or id
     * @param  string $field       identifier|entity_id
     * @return AttributepagesModel|false Current page model
     */
    public function initPagesInRegistry($pageId, $parentPageId = null, $field = 'identifier')
    {
        if (!$pageId) {
            return false;
        }

        $page = $this->coreRegistry->registry('attributepages_current_page');
        $parentPage = $this->coreRegistry->registry('attributepages_parent_page');
        $allowDirectOptionLink = $this->helper->isDirectLinkAllowed();

        if ($page) {
            if ($page->isOptionBasedPage() && !$parentPage && !$allowDirectOptionLink) {
                return false;
            }
            return $page;
        }

        if ($parentPage) {
            // if parent page is registered while current_page does not, it means that url is invalid
            return false;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $pageIds = [$pageId, htmlentities($pageId), $parentPageId, htmlentities($parentPageId)];
        $collection = $this->attrpagesCollectionFactory->create()
            ->addFieldToFilter('use_for_attribute_page', 1)
            ->addFieldToFilter(
                $field,
                [
                    'in' => array_unique(array_filter($pageIds))
                ]
            )
            ->joinStoreTable()
            ->setOrder('store_id', 'asc');

        // fix for the same identifiers for different options/pages
        $uniquePages = [];
        foreach ($collection as $page) {
            // fix for the case insensitive mysql query
            if (!in_array($page->getData($field), $pageIds)) {
                continue;
            }

            $key = $page->getOptionId() . $page->getIdentifier();

            if (!empty($uniquePages[$key])) {
                if (!in_array($storeId, $page->getStores())) {
                    continue;
                }
            }

            $uniquePages[$key] = $page;
        }

        $size = count($uniquePages);
        if (!$size) {
            return false;
        }

        // This logic fixes language switcher redirect when pages of different store views
        // has different idenntifiers
        $correctPages = [];
        foreach ($uniquePages as $key => $page) {
            if (in_array($storeId, $page->getStores())) {
                continue;
            }

            $correctCollection = $this->attrpagesCollectionFactory->create()
                ->addFieldToFilter('use_for_attribute_page', 1)
                ->addFieldToFilter('attribute_id', $page->getAttributeId())
                ->addStoreFilter($storeId);

            if ($page->getOptionId()) {
                $correctCollection->addFieldToFilter('option_id', $page->getOptionId());
            } else {
                $correctCollection->addFieldToFilter('option_id', ['null' => true]);
            }

            foreach ($correctCollection as $correctPage) {
                if (!empty($correctPages[$key])) {
                    if (!in_array($storeId, $correctPage->getStores())) {
                        continue;
                    }
                }
                $correctPages[$key] = $correctPage;
            }
        }

        // register parent page and remove all attr-based pages from array
        if ($parentPageId) {
            $parentPageIds = [$parentPageId, htmlentities($parentPageId)];
            foreach ($uniquePages as $key => $page) {
                if (!$page->isAttributeBasedPage()) {
                    continue;
                }

                if (in_array($page->getData($field), $parentPageIds)) {
                    $this->coreRegistry->unregister('attributepages_parent_page');
                    $this->coreRegistry->register('attributepages_parent_page', $correctPages[$key] ?? $page);
                }

                unset($uniquePages[$key]);
            }
        }

        // search for current page
        $currentPageIds = [$pageId, htmlentities($pageId)];
        foreach ($uniquePages as $key => $page) {
            if (in_array($page->getData($field), $currentPageIds)) {
                $this->coreRegistry->register('attributepages_current_page', $correctPages[$key] ?? $page);
                break;
            }
        }

        if (!$page = $this->coreRegistry->registry('attributepages_current_page')) {
            return false;
        }

        if ($parent = $this->coreRegistry->registry('attributepages_parent_page')) {
            // disallow links like brands/color or black/white or black/htc
            if ($parent->isOptionBasedPage() || $page->isAttributeBasedPage()) {
                return false;
            }
            // disallow links like color/htc or brands/white
            if ($parent->getAttributeId() !== $page->getAttributeId()) {
                return false;
            }
            $page->setParentPage($parent);
        }

        // disallow direct link to option page: example.com/htc
        if ($page->isOptionBasedPage() && !$parent && !$allowDirectOptionLink) {
            return false;
        }

        // root category is always registered as current_category
        $categoryId = $page->getCategoryId();
        if ($categoryId && !$this->coreRegistry->registry('current_category')) {
            $category = $this->categoryFactory->create()
                ->setStoreId($storeId)
                ->load($categoryId);
            $this->coreRegistry->register('current_category', $category);

            $category->setName($page->getPageTitle() ?: $page->getTitle());
        }

        return $page;
    }

    /**
     * Get object saved in registry
     * @param  String $id
     * @return mixed
     */
    public function getRegistryObject($id)
    {
        return $this->coreRegistry->registry($id);
    }
}
