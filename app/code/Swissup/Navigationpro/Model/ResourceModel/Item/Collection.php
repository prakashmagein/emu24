<?php

namespace Swissup\Navigationpro\Model\ResourceModel\Item;

use Magento\Store\Model\Store;
use Swissup\Navigationpro\Model\Item;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * @var integer
     */
    protected $storeId = 0;

    /**
     * @var boolean
     */
    protected $addRemoteEntitiesFlag = false;

    /**
     * @var boolean
     */
    protected $useFallbackStoreId = true;

    protected bool $addContentFields = true;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _construct()
    {
        $this->_init(
            'Swissup\Navigationpro\Model\Item',
            'Swissup\Navigationpro\Model\ResourceModel\Item'
        );
    }

    /**
     * Add filter by parentId
     *
     * @param integer $parentId
     * @return $this
     */
    public function addFilterByParentId($parentId)
    {
        if (!$parentId) {
            $this->addFieldToFilter('parent_id', ['null' => true]);
        } else {
            $this->addFieldToFilter('parent_id', $parentId);
        }

        return $this;
    }

    /**
     * Add filter by remote entity
     *
     * @param integer $entityType
     * @param integer $entityId
     * @return \Swissup\Navigationpro\Model\ResourceModel\Item\Collection
     */
    public function addFilterByRemoteEntity($entityType, $entityId)
    {
        $this->addFieldToFilter('remote_entity_id', $entityId)
            ->addFieldToFilter('remote_entity_type', $entityType);

        return $this;
    }

    /**
     * Add filter by category
     *
     * @param integer $categoryId
     * @return \Swissup\Navigationpro\Model\ResourceModel\Item\Collection
     */
    public function addFilterByCategory($categoryId)
    {
        $this->addFilterByRemoteEntity(
            \Swissup\Navigationpro\Model\Item::REMOTE_ENTITY_TYPE_CATEGORY,
            $categoryId
        );

        return $this;
    }

    /**
     * Set store Id
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get store Id
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->getStoreId());
    }

    /**
     * @param  boolean $flag
     * @return mixed [$this|bool]
     */
    public function canAddRemoteEntities($flag = null)
    {
        if (null === $flag) {
            return $this->addRemoteEntitiesFlag;
        }

        $this->addRemoteEntitiesFlag = $flag;

        return $this;
    }

    /**
     * @param  boolean $flag
     * @return mixed [$this|bool]
     */
    public function canUseFallbackStoreId($flag = null)
    {
        if (null === $flag) {
            return $this->useFallbackStoreId;
        }

        $this->useFallbackStoreId = $flag;

        return $this;
    }

    public function canAddContentFields($flag = null)
    {
        if (null === $flag) {
            return $this->addContentFields;
        }

        $this->addContentFields = $flag;

        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        if ($this->canAddContentFields()) {
            $this->addContentFieldsToResult();
        }

        if ($this->canAddRemoteEntities()) {
            $this->addRemoteEntitiesToResult();
        }

        return parent::_afterLoad();
    }

    /**
     * Add scope-sensitive data for both default and current stores
     *
     * $return void
     */
    public function addContentFieldsToResult()
    {
        $ids = $this->getColumnValues('item_id');
        if (!count($ids)) {
            return;
        }

        $storeIds = [$this->getStoreId()];
        if ($this->canUseFallbackStoreId()) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['content' => $this->getTable('swissup_navigationpro_item_content')])
            ->where('content.item_id IN (?)', $ids)
            ->where('content.store_id IN (?)', $storeIds);

        $result = $connection->fetchAll($select);
        if (!$result) {
            return;
        }

        $assocData = [];
        foreach ($result as $data) {
            if ($data['dropdown_settings']) {
                $data['dropdown_settings'] = $this->jsonHelper->jsonDecode(
                    $data['dropdown_settings']
                );
            } else {
                $data['dropdown_settings'] = [
                    'use_menu_settings' => '1'
                ];
            }

            if (!isset($assocData[$data['item_id']])) {
                $assocData[$data['item_id']]['content'] = [
                    'default' => [],
                    'scope' => [],
                ];
            }

            if ($data['store_id'] == Store::DEFAULT_STORE_ID) {
                $assocData[$data['item_id']]['content']['default'] = $data;
            } else {
                $assocData[$data['item_id']]['content']['scope'] = $data;
            }
        }

        foreach ($this as $item) {
            $itemId = $item->getId();
            if (!isset($assocData[$itemId])) {
                continue;
            }

            $item->addData($assocData[$itemId]);
            $item->addContentData(
                $assocData[$itemId]['content']['default'],
                $assocData[$itemId]['content']['scope']
            );
        }
    }

    /**
     * Add remote entity objects to the collection
     *
     * @return void
     */
    public function addRemoteEntitiesToResult()
    {
        $ids = $this->getColumnValues('item_id');
        if (!count($ids)) {
            return;
        }

        // this code is taken from Magento\Catalog\Plugin\Block\Topmenu::getCategoryTree
        $collection = $this->categoryCollectionFactory->create();
        $collection
            ->setStoreId($this->getStoreId())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('is_active')
            ->addUrlRewriteToResult()
            ->addOrder('level', self::SORT_ORDER_ASC)
            ->addOrder('position', self::SORT_ORDER_ASC)
            ->addOrder('parent_id', self::SORT_ORDER_ASC)
            ->addOrder('entity_id', self::SORT_ORDER_ASC);

        $path = '1/%';
        if ($rootId = $this->getStore()->getRootCategoryId()) {
            $path = '1/' . $rootId . '/%';
        }
        $collection->addFieldToFilter('path', ['like' => $path]);

        $items = $this->getItemsByColumnValue('remote_entity_type', Item::REMOTE_ENTITY_TYPE_CATEGORY);
        foreach ($items as $item) {
            $category = $collection->getItemById($item->getRemoteEntityId());
            if (!$category) {
                continue;
            }
            $item->setRemoteEntity($category);
        }
    }
}
