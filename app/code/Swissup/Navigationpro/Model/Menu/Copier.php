<?php

namespace Swissup\Navigationpro\Model\Menu;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Swissup\Navigationpro\Model\ItemFactory;
use Swissup\Navigationpro\Model\Menu;
use Swissup\Navigationpro\Model\MenuFactory;

class Copier
{
    private MenuFactory $menuFactory;

    private ItemFactory $itemFactory;

    private ResourceConnection $resource;

    public function __construct(
        MenuFactory $menuFactory,
        ItemFactory $itemFactory,
        ResourceConnection $resource
    ) {
        $this->menuFactory = $menuFactory;
        $this->itemFactory = $itemFactory;
        $this->resource = $resource;
    }

    public function copy($menuId): Menu
    {
        $menu = $this->menuFactory->create()->load($menuId);
        if (!$menu->getId()) {
            throw new NoSuchEntityException();
        }

        $menuCopy = $this->menuFactory->create()
            ->setData($menu->getData())
            ->setIsDuplicate(true)
            ->setIdentifier($menu->getIdentifier())
            ->setId(null);

        do {
            $identifier = $menuCopy->getIdentifier();
            $identifier = preg_match('/(.*)-(\d+)$/', $identifier, $matches)
                ? $matches[1] . '-' . ($matches[2] + 1)
                : $identifier . '-1';
            $menuCopy->setIdentifier($identifier);

            try {
                $menuCopy->save();
                break;
            } catch (AlreadyExistsException $e) {
                //
            }
        } while (true);

        try {
            $this->copyMenuItems($menu, $menuCopy);
        } catch (\Exception $e) {
            $menuCopy->delete();
            throw $e;
        }

        return $menuCopy;
    }

    private function copyMenuItems($from, $to)
    {
        $items = $from->getItems(0)->canAddRemoteEntities(false)->canAddContentFields(false);

        $itemContentTable = $this->resource->getTableName('swissup_navigationpro_item_content');
        $connection = $this->resource->getConnection();
        $contentItems = $connection->fetchAll(
            $connection->select()
                ->from($itemContentTable)
                ->where('item_id IN (?)', $items->getColumnValues('item_id'))
        );

        $itemsMap = [];
        foreach ($items as $item) {
            $itemCopy = $this->itemFactory->create()
                ->setData($item->getData())
                ->setMenuId($to->getId())
                ->setId(null)
                ->setPath(null)
                ->setSkipContentUpdate(true);

            if (isset($itemsMap[$itemCopy->getParentId()])) {
                $itemCopy->setParentItem($itemsMap[$itemCopy->getParentId()]);
                $itemCopy->setParentId($itemCopy->getParentItem()->getId());
            }

            $itemCopy->save();
            $itemsMap[$item->getId()] = $itemCopy;

            $contentItemsCopy = [];
            foreach ($contentItems as $i => $contentItem) {
                if ($contentItem['item_id'] != $item->getId()) {
                    continue;
                }
                $contentItem['item_id'] = $itemCopy->getId();
                $contentItemsCopy[] = $contentItem;
                unset($contentItems[$i]);
            }

            if ($contentItemsCopy) {
                $connection->insertMultiple($itemContentTable, $contentItemsCopy);
            }
        }
    }
}
