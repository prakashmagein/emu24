<?php
namespace Swissup\Attributepages\Model\ResourceModel;

use \Swissup\Attributepages\Model\Entity as AttributepagesEntity;
use \Magento\Framework\Exception\AlreadyExistsException;
/**
 * Attributepages Entity mysql resource
 */
class Entity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Swissup\Attributepages\Helper\Data
     */
    protected $helper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Swissup\Attributepages\Helper\Data $helper,
        $connectionName = null)
    {
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context, $connectionName);
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_attributepages_entity', 'entity_id');
    }
    /**
     * Process data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Swissup\Attributepages\Model\ResourceModel\Entity
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['entity_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('swissup_attributepages_store'), $condition);
        return parent::_beforeDelete($object);
    }
    /**
     * Prepare serialized_configuration column
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->getIsUniquePageToStores($object)) {
            throw new AlreadyExistsException(__('A page URL key for specified store already exists.'));
        }

        // remove spaces from url
        $url = $object->getIdentifier();
        $url = str_replace([' ', '+'], '-', $url);
        $object->setIdentifier($url);
        $options = $object->getExcludedOptionIds();
        if (is_array($options)) {
            $object->setExcludedOptionIds(
                implode(AttributepagesEntity::DELIMITER, $options)
            );
        } elseif ($options && strstr($options, '&')) { // grid serializer uses & to concat values
            $object->setExcludedOptionIds(
                str_replace('&', AttributepagesEntity::DELIMITER, $options)
            );
        }
        $displaySettings = [
            'display_mode',
            'listing_mode',
            'column_count',
            'group_by_first_letter',
            'image_width',
            'image_height',
            'use_layered_navigation',
            'options_root_template',
            'options_display_mode',
            'options_title',
        ];
        $data = [];
        foreach ($displaySettings as $key) {
            if ($object->hasData($key)) {
                $data[$key] = $object->getData($key);
            }
        }
        $object->setDisplaySettings($this->jsonHelper->jsonEncode($data));

        return parent::_beforeSave($object);
    }
    /**
     * Perform operations after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        $table = $this->getTable('swissup_attributepages_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['entity_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['entity_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }
    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return Swissup\Attributepages\Model\ResourceModel\Entity
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }
        return parent::load($object, $value, $field);
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
        }
        return parent::_afterLoad($object);
    }
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Swissup\Attributepages\Model\Data $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID];
            $select->join(
                    ['entity_store' => $this->getTable('swissup_attributepages_store')],
                    $this->getMainTable() . '.entity_id = entity_store.entity_id',
                    ['store_id']
                )
                ->where('use_for_attribute_page = ?', 1)
                ->where('entity_store.store_id in (?)', $stores)
                ->order('entity_store.store_id DESC')
                ->limit(1);
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
            $this->getTable('swissup_attributepages_store'),
            'store_id'
        )->where(
            'entity_id = :entity_id'
        );
        $binds = [':entity_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }
    /**
     * Check identifier to be unique for page to selected store(s).
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function getIsUniquePageToStores(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->storeManager->isSingleStoreMode() || !$object->hasStores()) {
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getLoadByIdentifierSelect($object->getData('identifier'), $stores);

        if ($object->getId()) {
            $select->where('entity_store.entity_id <> ?', $object->getId());
        }

        if (!$this->helper->isDirectLinkAllowed()) {
            if ($object->getOptionId()) {
                $select->where('entity.option_id IS NOT NULL');
            } else {
                $select->where('entity.option_id IS NULL');
            }
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }
    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Varien_Db_Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()->select()
            ->from(['entity' => $this->getMainTable()])
            ->join(
                ['entity_store' => $this->getTable('swissup_attributepages_store')],
                'entity.entity_id = entity_store.entity_id',
                [])
            ->where('entity.identifier = ?', $identifier)
            ->where('entity_store.store_id IN (?)', $store);
        if (!is_null($isActive)) {
            $select->where('entity.use_for_attribute_page = ?', $isActive);
        }
        return $select;
    }
    /**
     * Check if entity identifier exist for specific store
     * return entity id if entity exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('entity.entity_id')
            ->order('entity_store.store_id DESC')
            ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }
}
