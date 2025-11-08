<?php

namespace Swissup\Highlight\Helper;

class Conditions extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->catalogLayer = $layerResolver->get();
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Get current category ids (multiple filters are supported)
     *
     * @param  boolean $withChildren
     * @return array
     */
    public function getCurrentCategoryIds($withChildren = false)
    {
        $categoryIds = [];
        foreach ($this->catalogLayer->getState()->getFilters() as $filter) {
            if ($filter->getFilter()->getRequestVar() === 'cat') {
                $categoryIds[] = $filter->getValue();
            }
        }

        $currentCategory = $this->catalogLayer->getCurrentCategory();
        if (!$categoryIds && $currentCategory) {
            $categoryIds[] = $currentCategory->getId();
        }

        if ($categoryIds && $withChildren) {
            $paths = $this->categoryCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $categoryIds])
                ->getColumnValues('path');

            $collection = $this->categoryCollectionFactory->create();
            foreach ($paths as $path) {
                $collection->getSelect()->orWhere('path LIKE ?', $path . '/%');
            }

            $categoryIds = array_merge(
                $categoryIds,
                $collection->getColumnValues('entity_id')
            );
        }

        return $categoryIds;
    }
}
