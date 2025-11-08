<?php

namespace Swissup\Attributepages\Plugin;

class FilterList
{
    /**
     * @var \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     */
    private $pageViewHelper;

    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    private $attributepagesCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attributepagesCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\Attributepages\Helper\Page\View $pageViewHelper,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attributepagesCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->pageViewHelper = $pageViewHelper;
        $this->attributepagesCollectionFactory = $attributepagesCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param array $filters
     * @return array
     */
    public function afterGetFilters(
        \Magento\Catalog\Model\Layer\FilterList $subject,
        $filters
    ) {
        $filters = $this->removeHiddenFilters($filters);

        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');
        if (!$currentPage) {
            return $filters;
        }

        foreach ($filters as $key => $filter) {
            if ($filter instanceof \Magento\CatalogSearch\Model\Layer\Filter\Category ||
                $filter instanceof \Swissup\Ajaxlayerednavigation\Model\Layer\Filter\Category
            ) {
                unset($filters[$key]);
            }
        }

        return $filters;
    }

    private function removeHiddenFilters($filters)
    {
        $pages = $this->attributepagesCollectionFactory->create()
            ->addAttributeOnlyFilter()
            ->addUseForAttributePageFilter()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addFieldToFilter('hide_from_layered_navigation', 1);
        $ids = $pages->getColumnValues('attribute_id');

        if (!$ids) {
            return $filters;
        }

        foreach ($filters as $key => $filter) {
            if (!$filter->hasData('attribute_model')) {
                continue;
            }

            $id = $filter->getAttributeModel()->getAttributeId();
            if (in_array($id, $ids)) {
                unset($filters[$key]);
            }
        }

        return $filters;
    }
}
