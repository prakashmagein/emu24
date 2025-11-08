<?php
namespace Swissup\Askit\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class AbstractMassStatus
 */
abstract class AbstractMassStatus extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'main_table.id';

    /**
     * Resource collectionClass
     *
     * @var string
     */
    protected $collectionClass = \Swissup\Askit\Model\ResourceModel\Message\Collection::class;

    /**
     * Message status
     *
     * @var bool
     */
    protected $status = true;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');

        try {
            if (isset($excluded)) {
                if (!empty($excluded) && 'false' != $excluded) {
                    $this->excludedSetStatus($excluded);
                } else {
                    $this->setStatusAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedSetStatus($selected);
            } else {
                $this->messageManager->addErrorMessage(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     *
     * @return \Swissup\Askit\Model\ResourceModel\Message\Collection
     */
    protected function getCollection()
    {
        $collection = $this->_objectManager->get($this->collectionClass);
        // $collection = $this->_collectionFactory->create();

        $request = $this->getRequest();
        // $customerId = $this->getCustomerId();
        // if ($customerId) {
        //     $collection->addCustomerFilter($customerId);
        // }

        $productId = $this->getRequest()->getParam('current_product_id', false);
        if ($productId) {
            $collection->addProductFilter($productId);
        }

        $categoryId = $this->getRequest()->getParam('current_category_id', false);
        if ($categoryId) {
            $collection->addCategoryFilter($categoryId);
        }

        $pageId = $this->getRequest()->getParam('current_page_id', false);
        if ($pageId) {
            $collection->addPageFilter($pageId);
        }

        return $collection;
    }

    /**
     * Set status to all
     *
     * @return void
     * @throws \Exception
     */
    protected function setStatusAll()
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();
        $this->setStatus($collection);
    }

    /**
     * Set status to all but the not selected
     *
     * @param array $excluded
     * @return void
     * @throws \Exception
     */
    protected function excludedSetStatus(array $excluded)
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setStatus($collection);
    }

    /**
     * Set status to selected items
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedSetStatus(array $selected)
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setStatus($collection);
    }

    /**
     * Set status to collection items
     *
     * @param \Swissup\Askit\Model\ResourceModel\Message\Collection $collection
     * @return void
     */
    protected function setStatus(\Swissup\Askit\Model\ResourceModel\Message\Collection $collection)
    {
        foreach ($collection as $item) {
            $item->setIsPrivate($this->status);
            $item->save();
        }
    }
}
