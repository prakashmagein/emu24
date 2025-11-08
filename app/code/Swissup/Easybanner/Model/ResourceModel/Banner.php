<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * Banner mysql resource
 */
class Banner extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
     /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['sizes' => [[], []]];

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_date = $date;
        $this->_storeManager = $storeManager;
    }

    /**
     * Load an object using `identifier`
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_banner', 'banner_id');
    }

    /**
     * Process banner data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Swissup\Easybanner\Model\ResourceModel\Banner
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['banner_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('swissup_easybanner_banner_store'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /*
        ** Save Banner Stores
         */
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        $table = $this->getTable('swissup_easybanner_banner_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['banner_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['banner_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        /*
        ** Save Banner Placeholders
         */
        $oldPlaceholders = $this->lookupPlaceholderIds($object->getId());
        $newPlaceholders = (array)$object->getPlaceholders();
        $newPlaceholders = array_filter($newPlaceholders);
        $table = $this->getTable('swissup_easybanner_banner_placeholder');
        $insert = array_diff($newPlaceholders, $oldPlaceholders);
        $delete = array_diff($oldPlaceholders, $newPlaceholders);
        if ($delete) {
            $where = ['banner_id = ?' => (int)$object->getId(), 'placeholder_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $placeholderId) {
                $data[] = ['banner_id' => (int)$object->getId(), 'placeholder_id' => (int)$placeholderId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
            $placeholders = $this->lookupPlaceholderIds($object->getId());
            $object->setData('placeholders', $placeholders);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Swissup\Easybanner\Model\Banner $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID];
            $select->join(
                ['sebs' => $this->getTable('swissup_easybanner_banner_store')],
                $this->getMainTable() . '.banner_id = sebs.banner_id',
                ['store_id']
            )->where(
                'sebs.store_id in (?)',
                $stores
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }
        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_easybanner_banner_store'),
            'store_id'
        )->where(
            'banner_id = :banner_id'
        );
        $binds = [':banner_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }


    /**
     * Get placeholder ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupPlaceholderIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_easybanner_banner_placeholder'),
            'placeholder_id'
        )->where(
            'banner_id = :banner_id'
        );
        $binds = [':banner_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }

    public function getOptionBanners()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable())
        ->where('status = ?', 1);
        return $connection->fetchAll($select);
    }

    public function getClicksCount($bannerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_easybanner_banner_statistic'),
            ['clicks' => 'SUM(clicks_count)']
        )
        ->where('banner_id = ?', $bannerId);
        $counts = $connection->fetchCol($select);
        if (!$counts[0]) {
            return 0;
        }
        return $counts[0];
    }

    public function getDisplayCount($bannerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_easybanner_banner_statistic'),
            ['display' => 'SUM(display_count)']
        )
        ->where('banner_id = ?', $bannerId);
        $counts = $connection->fetchCol($select);
        if (!$counts[0]) {
            return 0;
        }
        return $counts[0];
    }
}
