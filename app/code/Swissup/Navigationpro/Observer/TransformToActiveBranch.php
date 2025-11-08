<?php

namespace Swissup\Navigationpro\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TransformToActiveBranch implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $widgetParameters = $observer->getData('widget_parameters');

        $isShowActiveBranch = $widgetParameters ? !!$widgetParameters->getShowActiveBranch() : true;

        if (!$isShowActiveBranch) {
            return;
        }

        /** @var \Swissup\Navigationpro\Model\Menu $menu */
        $menu = $observer->getMenu();

        $storeId = $observer->getStoreId();

        $categoryId = $observer->getCategoryId();
        if (!$categoryId) {
            return;
        }
        /** @var \Swissup\Navigationpro\Model\Item $currentItem */
        $currentItem = $menu
            ->getItems($storeId)
            ->canAddRemoteEntities(false)
            ->addFilterByCategory($categoryId)
            ->setPageSize(1)
            ->getFirstItem();

        $filterMap = ['path'];
        $filterValues = [
            ['like' => $currentItem->getPath() . '/%']
        ];
        $isShowParent = $widgetParameters ?
            ($widgetParameters->getShowParent() === null || $widgetParameters->getShowParent()) : true;

        if ($isShowParent) {
            $filterMap['parent_item'] = 'item_id';
            $filterMap['current_item'] = 'item_id';
            $filterValues['parent_item'] = $currentItem->getParentId();
            $filterValues['current_item'] = $currentItem->getId();
        }

        // get all children
        $collection = $observer->getCollection();

        $collection
            ->addFieldToFilter('level', ['lteq' => $currentItem->getLevel() + 1])
            ->addFieldToFilter($filterMap, $filterValues);

        // change parent_id value to render all items together
        $parentId = $collection->getFirstItem()->getParentId();
        foreach ($collection as $item) {
            $item->setParentId($parentId);
        }

        // update css classes
        $parentItem = $collection->getItemById($currentItem->getParentId());
        if ($parentItem) {
            $parentItem->setCssClass($parentItem->getCssClass() . ' navpro-back');
        }
        $activeItem = $collection->getItemById($currentItem->getId());
        if ($activeItem) {
            $activeItem->setCssClass($activeItem->getCssClass() . ' navpro-current');
        }
    }
}
