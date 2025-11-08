<?php

namespace Swissup\Navigationpro\Model\ResourceModel;

use Swissup\Navigationpro\Model\Item as MenuItem;
use Swissup\Navigationpro\Model\Menu\Source\CategoryImportMode;
use Magento\Framework\Exception\LocalizedException;

class Menu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Swissup\Navigationpro\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $cacheState;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Swissup\Navigationpro\Model\ItemFactory $itemFactory
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Swissup\Navigationpro\Model\ItemFactory $itemFactory,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        $connectionName = null
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->itemFactory = $itemFactory;
        $this->configValueFactory = $configValueFactory;
        $this->cacheState = $cacheState;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context, $connectionName);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_navigationpro_menu', 'menu_id');
    }

    /**
     * Prepare dropdown_settings column
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $dropdownSettings = $object->getDropdownSettings();
        if (is_array($dropdownSettings)) {
            $dropdownSettings = $this->jsonHelper->jsonEncode($dropdownSettings);
            $object->setDropdownSettings($dropdownSettings);
        }

        $itemSettings = $object->getItemSettings();
        if (is_array($itemSettings)) {
            $itemSettings = $this->jsonHelper->jsonEncode($itemSettings);
            $object->setItemSettings($itemSettings);
        }

        $modifiers = $object->getModifiers();
        if ($modifiers && is_array($modifiers)) {
            $object->setCssClass(
                $object->getCssClass()
                . ' '
                . implode(' ', $modifiers)
            );
        }

        $object->validateRecursiveCalls([
            'Dropdown Settings' => $dropdownSettings,
            'Item Settings' => $itemSettings,
        ]);

        return parent::_beforeSave($object);
    }

    /**
     * Sync config value with menu identifier
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $cleanCache = false;
        $newIdentifier = $object->getData('identifier');
        $oldIdentifier = $object->getOrigData('identifier');

        if ($oldIdentifier && $oldIdentifier !== $newIdentifier) {
            $collection = $this->configValueFactory->create()->getCollection()
                ->addFieldToFilter('path', \Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU)
                ->addFieldToFilter('value', $oldIdentifier);

            foreach ($collection as $config) {
                $cleanCache = true;
                $config->setValue($newIdentifier)->save();
            }
        }

        $scopes = $object->getData('config_scopes');
        if (is_array($scopes)) {
            $collection = $this->configValueFactory->create()->getCollection()
                ->addFieldToFilter('path', \Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU)
                ->addFieldToFilter('value', $newIdentifier);

            // 1. create new config entries
            foreach ($scopes as $scopeId) {
                $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
                if (strpos($scopeId, 'website_') === 0) {
                    $scopeId = str_replace('website_', '', $scopeId);
                    $scope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
                } elseif (!$scopeId) {
                    $scope = 'default';
                }

                $configItems = $collection->getItemsByColumnValue('scope_id', $scopeId);
                foreach ($configItems as $config) {
                    if ($config->getScope() == $scope) {
                        // Menu is already linked to the scope.
                        $collection->removeItemByKey($config->getId());
                        continue 2;
                    }
                }

                $cleanCache = true;
                $this->configValueFactory->create()
                    ->setPath(\Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU)
                    ->setValue($newIdentifier)
                    ->setScope($scope)
                    ->setScopeId($scopeId)
                    ->save();
            }

            // 2. remove non-used config entries
            // Do not use `foreach (collection as)` 'cos it's buggy when used after `removeItemByKey`
            foreach ($collection->getItems() as $config) {
                $cleanCache = true;
                $config->delete();
            }
        }

        if ($cleanCache) {
            $this->cleanCache();
        }

        return parent::_afterSave($object);
    }

    /**
     * Prepare dropdown_settings object
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $dropdownSettings = $object->getDropdownSettings();
        if ($dropdownSettings) {
            $object->setDropdownSettings($this->jsonHelper->jsonDecode($dropdownSettings));
        }

        $itemSettings = $object->getItemSettings();
        if ($itemSettings) {
            $object->setItemSettings($this->jsonHelper->jsonDecode($itemSettings));
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $cleanCache = false;
        $collection = $this->configValueFactory->create()->getCollection()
            ->addFieldToFilter('path', \Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU)
            ->addFieldToFilter('value', $object->getIdentifier());

        foreach ($collection as $config) {
            $cleanCache = true;
            $config->delete();
        }

        if ($cleanCache) {
            $this->cleanCache();
        }

        return parent::_afterDelete($object);
    }

    /**
     * Import category into menu inside parent item
     *
     * @param  integer $categoryId
     * @param  integer $parentItemId
     * @param  string  $mode @see Swissup\Navigationpro\Model\Menu\Source\CategoryImportMode
     * @return void
     */
    public function importCategory($categoryId, $menuId, $parentItemId, $mode)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        if (!$category->getId()) {
            return;
        }

        if ($category->getLevel() <= 1 && $mode !== CategoryImportMode::MODE_CHILDREN) {
            throw new LocalizedException(
                __("Use 'Children of Selected Item' mode when importing ROOT category")
            );
        }

        $parentItem = $this->itemFactory->create();
        if ($parentItemId) {
            $parentItem->load($parentItemId);
            if (!$parentItem->getId()) {
                return;
            }
        } else {
            $parentItem->addData([
                'menu_id' => $menuId,
                'level' => 1,
                'path'  => 0,
            ]);
        }

        $filter = [];
        $filterChildren = [
            'attribute' => 'path',
            'like' => $category->getPath() . '/%'
        ];
        $filterSelected = [
            'attribute' => 'entity_id',
            'eq' => $category->getId()
        ];
        switch ($mode) {
            case CategoryImportMode::MODE_CHILDREN:
                $filter[] = $filterChildren;
                break;
            case CategoryImportMode::MODE_SELECTED:
                $filter[] = $filterSelected;
                break;
            default:
                $filter[] = $filterChildren;
                $filter[] = $filterSelected;
                break;
        }

        $collection = $this->categoryCollectionFactory->create()
            ->addNameToResult()
            ->addUrlRewriteToResult()
            ->addAttributeToFilter($filter)
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('include_in_menu')
            ->addAttributeToSort('level')
            ->addAttributeToSort('position')
            ->addAttributeToSort('parent_id')
            ->addAttributeToSort('entity_id');

        if ($collection->getSize()) {
            // create mapping to speedup items processing
            // @see \Swissup\Navigationpro\Model\Item@getParentItem
            $mapping = [
                $collection->getFirstItem()->getParentId() => $parentItem
            ];
            foreach ($collection as $category) {
                if (!isset($mapping[$category->getParentId()])) {
                    throw new LocalizedException(
                        __("Incorrect 'level' value of category ID=%1", $category->getId())
                    );
                }

                $item = $this->itemFactory->create()
                    ->setParentItem($mapping[$category->getParentId()])
                    ->addCategoryEntityData($category);

                $item->addData([
                        'menu_id' => $menuId,
                        'parent_id' => $item->getParentItem()->getId(),
                    ])
                    ->save();

                $mapping[$category->getId()] = $item;
            }
        }
    }

    /**
     * Clean caches when assigning new menu to the store
     * or changing menu identifier.
     *
     * @return void
     */
    private function cleanCache()
    {
        $cacheTypes = [
            \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
        ];

        foreach ($cacheTypes as $cacheType) {
            if (!$this->cacheState->isEnabled($cacheType)) {
                continue;
            }
            $this->cacheTypeList->cleanType($cacheType);
        }
    }
}
