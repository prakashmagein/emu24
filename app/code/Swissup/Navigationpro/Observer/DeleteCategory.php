<?php

namespace Swissup\Navigationpro\Observer;

use Swissup\Navigationpro\Model\Item;
use Magento\Framework\Event\ObserverInterface;

class DeleteCategory implements ObserverInterface
{
    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @param \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * Remove all items related to deletec category
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        /** @var \Swissup\Navigationpro\Model\ResourceModel\Item\Collection $collection */
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('remote_entity_type', Item::REMOTE_ENTITY_TYPE_CATEGORY)
            ->addFieldToFilter('remote_entity_id', $category->getId());

        foreach ($collection as $item) {
            $item->delete();
        }
    }
}
