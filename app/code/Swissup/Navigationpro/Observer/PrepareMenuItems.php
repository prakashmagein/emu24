<?php

namespace Swissup\Navigationpro\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\Navigationpro\Model\Item;

class PrepareMenuItems implements ObserverInterface
{
    /**
     * System event manager
     *
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Initialize dependencies.
     *
     * @param Resolver $layerResolver
     */
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $menu = $observer->getMenu();
        $menuTreeRootNode =  $observer->getMenuTreeRootNode();

        $storeId = $observer->getStoreId();

        /** @var \Swissup\Navigationpro\Model\ResourceModel\Item\Collection $collection */
        $collection = $menu->getVisibleItems($storeId);

        $currentCategory = $observer->getCurrentCategory();
        $categoryId = $observer->getCategoryId();
        if (!$categoryId && $currentCategory) {
            $categoryId = $currentCategory->getId();
        }

        $widgetParameters = $observer->getData('widget_parameters');

        // Allow to modify collection for various customizations
        $this->eventManager->dispatch(
            'swissup_navigationpro_menu_prepare_collection_load_before',
            [
                'collection' => $collection,
                'menu' => $menu,
                'store_id' => $storeId,
                'category_id' => $categoryId,
                'widget_parameters' => $widgetParameters,
            ]
        );

        $rootId = 0;
        if ($parentId = $collection->getFirstItem()->getParentId()) {
            $rootId = $parentId;
        }

        $activeNode = false;
        $mapping = [$rootId => $menuTreeRootNode];

        /** @var \Swissup\Navigationpro\Model\Item $item */
        foreach ($collection as $item) {
            $parentId = $item->getParentId();
            if (!$parentId) {
                $parentId = 0;
            }
            if (!isset($mapping[$parentId])) {
                continue;
            }

            if ($item->isCategoryItem()) {
                if (!$item->getRemoteEntity() ||
                    !$item->getRemoteEntity()->getIsActive()
                ) {
                    continue;
                }
            }

            /** @var Node $parentItemNode */
            $parentItemNode = $mapping[$parentId];

            $itemNode = new \Swissup\Navigationpro\Data\Tree\Node(
                $this->getItemAsArray($item, $currentCategory),
                'id',
                $parentItemNode->getTree(),
                $parentItemNode
            );
            $parentItemNode->addChild($itemNode);

            $mapping[$item->getId()] = $itemNode;

            if ($itemNode->getIsActive()) {
                $activeNode = $itemNode;
            }
        }

        if ($activeNode) {
            foreach ($mapping as $node) {
                if (!$activeNode->getPathId() ||
                    !$node->getPathId() ||
                    $node->getPathLevel() > $activeNode->getPathLevel()
                ) {
                    continue;
                }

                if (strpos($activeNode->getPathId(), $node->getPathId()) === 0) {
                    $node->setHasActive(true);
                }
            }
        }
    }

    /**
     * Convert item to array
     *
     * @param \Swissup\Navigationpro\Model\Item $item
     * @param \Magento\Catalog\Model\Category $currentCategory
     * @return array
     */
    protected function getItemAsArray($item, $currentCategory)
    {
        $hasActive = false;
        $remoteEntityId = $item->getRemoteEntityId();
        $currentCategoryPath = $currentCategory ? $currentCategory->getPath() : null;

        if ($remoteEntityId && $currentCategoryPath) {
            $hasActive = in_array($remoteEntityId, explode('/', $currentCategoryPath));
        }

        return [
            'id'   => 'item-node-' . $item->getId(),
            'path_id'   => $item->getPath(),
            'path_level' => $item->getPath() ? count(explode('/', $item->getPath())) - 1 : 0,
            'name' => $item->getName(),
            'html' => $item->getHtml(),
            'url'  => $item->getUrl(),
            'url_path'  => $item->getUrlPath(),
            'css_class' => $item->getCssClass(),
            'dropdown_settings' => $item->getDropdownSettings(),
            'has_active' => $hasActive,
            'is_active'  => $currentCategory && $remoteEntityId == $currentCategory->getId(),
            'is_category' => $item->isCategoryItem(),
            'remote_entity' => $item->getRemoteEntity()
        ];
    }
}
