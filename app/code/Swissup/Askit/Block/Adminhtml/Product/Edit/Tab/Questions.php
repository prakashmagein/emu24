<?php
namespace Swissup\Askit\Block\Adminhtml\Product\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Questions extends \Swissup\Askit\Block\Adminhtml\Question\Grid
{
     /**
      * Prepare collection
      *
      * @inherit
      */
    protected function _prepareCollection()
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Question\Collection */
        $collection = $this->_collectionFactory->create();

        $customerId = $this->getCustomerId();
        if ($customerId) {
            $collection->addCustomerFilter($customerId);
        }

        $productId = $this->getProductId();
        if ($productId) {
            $collection->addProductFilter($productId);
        }

        $categoryId = $this->getCategoryId();
        if ($categoryId) {
            $collection->addCategoryFilter($categoryId);
        }

        $pageId = $this->getPageId();
        if ($pageId) {
            $collection->addPageFilter($pageId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
}
