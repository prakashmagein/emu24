<?php

namespace Swissup\Navigationpro\Observer;

use Magento\Store\Model\Store;
use Magento\Framework\Event\ObserverInterface;
use Swissup\Navigationpro\Model\Item;
use Swissup\Navigationpro\Model\Menu;

class SaveCategory implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var \Swissup\Navigationpro\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Swissup\Navigationpro\Model\MenuRepository
     */
    private $menuRepository;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Swissup\Navigationpro\Model\ItemFactory $itemFactory
     * @param \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Swissup\Navigationpro\Model\MenuRepository $menuRepository
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Swissup\Navigationpro\Model\ItemFactory $itemFactory,
        \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Swissup\Navigationpro\Model\MenuRepository $menuRepository
    ) {
        $this->cache = $cache;
        $this->itemFactory = $itemFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->menuRepository = $menuRepository;
    }

    /**
     * Add new category items to existing menu's
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        /** @var \Swissup\Navigationpro\Model\ResourceModel\Item\Collection $items */
        $items = $this->itemCollectionFactory->create()
            ->addFieldToFilter('remote_entity_type', Item::REMOTE_ENTITY_TYPE_CATEGORY);

        if ($category->getLevel() > 2) {
            $items->addFieldToFilter('remote_entity_id', $category->getParentId());

            if ($category->isObjectNew()) {
                $this->createNestedItems($items, $category);
            } else {
                $this->updateMenuCache($items->getColumnValues('menu_id'));
            }
        } else {
            try {
                $siblings = $this->categoryRepository
                    ->get($category->getParentId())
                    ->getChildren();
            } catch (\Exception $e) {
                return;
            }

            if (!$siblings) {
                return;
            }

            $siblings = explode(',', $siblings);

            $items->addFieldToFilter('remote_entity_id', $siblings)
                ->getSelect()
                ->group('menu_id')
                ->having('MIN(level)');

            if ($category->isObjectNew()) {
                $processed = [];
                foreach ($items as $item) {
                    $key = $item->getMenuId() . '_' . $item->getLevel();

                    if (isset($processed[$key])) {
                        continue;
                    }

                    $processed[$key] = true;

                    if ($item->getLevel() > $category->getLevel()) {
                        $this->createNestedItems([$item->getParentItem()], $category);
                    } else {
                        $this->createRootItems([$item->getMenuId()], $category);
                    }
                }
            } else {
                $this->updateMenuCache($items->getColumnValues('menu_id'));
            }
        }
    }

    /**
     * @param \Swissup\Navigationpro\Model\ResourceModel\Item\Collection|array $parentItems
     * @param \Magento\Catalog\Model\Category $remoteEntity
     * @return void
     */
    protected function createNestedItems($parentItems, $remoteEntity)
    {
        foreach ($parentItems as $parentItem) {
            try {
                $this->menuRepository->getById($parentItem->getMenuId());
            } catch (\Exception $e) {
                continue;
            }

            $this->getItem($remoteEntity, $parentItem->getMenuId(), $parentItem)->save();
        }
    }

    /**
     * @param array $menuIds
     * @param \Magento\Catalog\Model\Category $remoteEntity
     * @return void
     */
    protected function createRootItems($menuIds, $remoteEntity)
    {
        foreach ($menuIds as $menuId) {
            try {
                $this->menuRepository->getById($menuId);
            } catch (\Exception $e) {
                continue;
            }

            $this->getItem($remoteEntity, $menuId)->save();
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $remoteEntity
     * @param integer $menuId
     * @param \Swissup\Navigationpro\Model\Item|null $parentItem
     * @return \Swissup\Navigationpro\Model\Item
     */
    protected function getItem($remoteEntity, $menuId, $parentItem = null)
    {
        $newItem = $this->itemFactory->create()
            ->addCategoryEntityData($remoteEntity)
            ->addData([
                'menu_id' => $menuId,
                'parent_id' => $parentItem ? $parentItem->getId() : null,
            ]);

        if ($parentItem) {
            $newItem->setParentItem($parentItem);
        }

        return $newItem;
    }

    protected function updateMenuCache($menuIds)
    {
        if (!$menuIds) {
            return;
        }

        $tags = [];

        foreach ($menuIds as $menuId) {
            $tags[] = Menu::CACHE_TAG . '_' . $menuId;
        }

        $this->cache->clean($tags);
    }
}
