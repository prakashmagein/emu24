<?php
namespace Swissup\Attributepages\Model\ResourceModel\Entity;

use Magento\Store\Model\Store;

/**
 * Attributepages Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Attributepages\Model\Entity', 'Swissup\Attributepages\Model\ResourceModel\Entity');
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['swissup_attributepages_store' => $this->getTable($tableName)])
                ->where('swissup_attributepages_store.' . $columnName . ' IN (?)', $items);
            $result = $connection->fetchAll($select);
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$columnName]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $entityId = $item->getData($columnName);
                    if (!isset($storesData[$entityId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$entityId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$entityId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', $storesData[$entityId]);
                }
            }
        }
    }
    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }
        return parent::addFieldToFilter($field, $condition);
    }
    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);
        return $this;
    }
    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }
        if (!is_array($store)) {
            $store = [$store];
        }
        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }
        $this->addFilter('store', ['in' => $store], 'public');
    }
    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        return $countSelect;
    }
    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('swissup_attributepages_store', 'entity_id');
        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->joinStoreTable();
        }

        parent::_renderFiltersBefore();
    }

    public function joinStoreTable()
    {
        $this->getSelect()->join(
            ['store_table' => $this->getTable('swissup_attributepages_store')],
            'main_table.entity_id = store_table.entity_id',
            []
        )->group('main_table.entity_id');

        return $this;
    }

    /**
     * @return Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    public function addOptionOnlyFilter()
    {
        $this->addFilter('option_id', ['notnull' => true], 'public');
        return $this;
    }
    /**
     * Add filter to receive attribute pages only
     *
     * @return Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    public function addAttributeOnlyFilter()
    {
        $this->addFilter('option_id', ['null' => true], 'public');
        return $this;
    }

    public function addUseForAttributePageFilter()
    {
        $this->addFilter('use_for_attribute_page', 1, 'public');
        return $this;
    }

    /**
     * Perform manual sorting by rendered title and sort_order.
     *
     * @param boolean $useSortOrder
     * @return $this
     */
    public function sortByRenderedTitle($useSortOrder = true)
    {
        $items = $this->getItems();

        usort($items, function ($a, $b) use ($useSortOrder) {
            if ($useSortOrder) {
                $aSort = (int) $a->getSortOrder();
                $bSort = (int) $b->getSortOrder();

                if ($aSort !== $bSort) {
                    return $aSort <=> $bSort;
                }
            }

            return strnatcasecmp($a->getTitle(), $b->getTitle());
        });

        $this->_items = $items;

        return $this;
    }
}
