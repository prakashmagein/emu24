<?php
namespace Swissup\Easybanner\Model\ResourceModel\Banner;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'banner_id';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

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
        $this->_init('Swissup\Easybanner\Model\Banner', 'Swissup\Easybanner\Model\ResourceModel\Banner');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add store_ids to items
     */
    protected function performAfterLoadStore()
    {
        $items = $this->getColumnValues('banner_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['banner_store' => $this->getTable('swissup_easybanner_banner_store')])
                ->where('banner_store.banner_id IN (?)', $items);

            $result = $connection->fetchAll($select);
            $stores = [];
            foreach ($result as $data) {
                $stores[$data['banner_id']][] = $data['store_id'];
            }

            $allStores = $this->storeManager->getStores(false, true);
            foreach ($stores as $bannerId => $storeIds) {
                $item = $this->getItemById($bannerId);
                if (!$item) {
                    continue;
                }
                if (in_array(0, $storeIds)) {
                    $storeId = current($allStores)->getId();
                    $storeCode = key($allStores);
                } else {
                    $storeId = current($storeIds);
                    $storeCode = $this->storeManager->getStore($storeId)->getCode();
                }

                $item->setData('_first_store_id', $storeId);
                $item->setData('store_code', $storeCode);
                $item->setData('store_id', $storeIds);
                $item->setData('stores', $storeIds);
            }
        }
    }

    /**
     * Add store_ids to items
     */
    protected function performAfterLoadPlaceholder()
    {
        $items = $this->getColumnValues('banner_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['banner_placeholder' => $this->getTable('swissup_easybanner_banner_placeholder')])
                ->where('banner_placeholder.banner_id IN (?)', $items);

            $result = $connection->fetchAll($select);
            $placeholders = [];
            foreach ($result as $data) {
                $placeholders[$data['banner_id']][] = $data['placeholder_id'];
            }

            foreach ($placeholders as $bannerId => $placeholderIds) {
                $item = $this->getItemById($bannerId);
                if (!$item) {
                    continue;
                }
                $item->setData('placeholders', $placeholderIds);
            }
        }
    }

    protected function performAfterLoadStat($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);

        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                    ['bs' => $this->getTable($tableName)],
                    [
                        'banner_id',
                        'display_count' => new \Zend_Db_Expr('SUM(`display_count`)'),
                        'clicks_count' => new \Zend_Db_Expr('SUM(`clicks_count`)')
                    ]
                )
                ->where($columnName . ' IN (?)', $items)
                ->group(
                    ['banner_id']
                );
            $statistics = $connection->fetchAssoc($select);
            if ($statistics) {
                foreach ($this as $item) {
                    $entityId = $item->getData($columnName);
                    if (isset($statistics[$entityId])) {
                        $item->setData('display_count', $statistics[$entityId]['display_count']);
                        $item->setData('clicks_count', $statistics[$entityId]['clicks_count']);
                    } else {
                        $item->setData('display_count', 0);
                        $item->setData('clicks_count', 0);
                    }
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
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }
        if (!is_array($store)) {
            $store = [$store];
        }
        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        $this->addFilter('store', ['in' => $store], 'public');
    }
    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
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
        $this->performAfterLoadStore();
        $this->performAfterLoadPlaceholder();
        $this->performAfterLoadStat('swissup_easybanner_banner_statistic', 'banner_id');
        return parent::_afterLoad();
    }
     /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('swissup_easybanner_banner_store', 'banner_id');
    }
}
