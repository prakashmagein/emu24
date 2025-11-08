<?php

namespace Swissup\Navigationpro\Ui\Component\Form\Menu;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Store;
use Swissup\Navigationpro\Model\Item;
use Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory;

class Tree implements OptionSourceInterface
{
    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface
     */
    protected $menuLocator;

    /**
     * @var array
     */
    protected $tree;

    /**
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface $locator
    ) {
        $this->request = $request;
        $this->menuLocator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getItemsTree();
    }

    /**
     * Retrieve items tree
     *
     * @return array
     */
    protected function getItemsTree()
    {
        if ($this->tree === null) {
            $menuItems = [
                'value'     => 0,
                'is_active' => 1,
                'level'     => 1,
                'path'      => 0,
                'label'     => 'Menu Items',
                'optgroup'  => [],
            ];
            $itemById = [
                -1 => [
                    'value'     => -1,
                    'level'     => 0,
                    'is_active' => 1,
                    'optgroup'  => [
                        &$menuItems
                    ],
                ],
                0 => &$menuItems,
            ];

            $storeId = $this->request->getParam('store', Store::DEFAULT_STORE_ID);
            $collection = $this->menuLocator->getMenu()->getItems($storeId);
            foreach ($collection as $item) {
                // fix for ui component rendering (it does not render element with id = null)
                if (!$parentId = $item->getParentId()) {
                    $parentId = 0;
                }

                foreach ([$item->getId(), $parentId] as $itemId) {
                    if (!isset($itemById[$itemId])) {
                        $itemById[$itemId] = ['value' => $itemId];
                    }
                }

                // mapping is used to prevent properties override in
                // Magento_Ui/js/form/element/ui-select component
                $keys = [
                    'level'     => 'level',
                    'path'      => 'db_path',
                    'position'  => 'position',
                    'parent_id' => 'parent_id',
                    'menu_id'   => 'menu_id',
                ];
                foreach ($keys as $from => $to) {
                    $itemById[$item->getId()][$to] = $item->getData($from);
                }

                $itemById[$item->getId()]['db_level'] = $item->getLevel();
                $itemById[$item->getId()]['is_active'] = $item->getIsActive();
                $itemById[$item->getId()]['label'] = $item->getName() ?
                    $item->getName() : $item->getId();
                $itemById[$parentId]['optgroup'][] = &$itemById[$item->getId()];
            }

            $this->tree = $itemById[-1]['optgroup'];
        }

        return $this->tree;
    }
}
