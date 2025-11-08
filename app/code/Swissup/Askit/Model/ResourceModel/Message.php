<?php
namespace Swissup\Askit\Model\ResourceModel;

use Swissup\Askit\Api\Data\MessageInterface;

/**
 * Askit Message mysql resource
 */
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Core Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $coreDate;

    /**
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @par am \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\User\Model\UserFactory $userFactory,
        $connectionName = null
    ) {
        $this->userFactory = $userFactory;
        $this->coreDate = $coreDate;

        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_askit_message', 'id');
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);

        if ($object->isObjectNew() && !$object->hasCreatedTime()) {
            $object->setCreatedTime($this->coreDate->gmtDate());
        }

        if ($object->isObjectNew() && !$object->hasParentId()) {
            $object->setParentId(0);
        }

        $object->setUpdateTime($this->coreDate->gmtDate());

        return $this;
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
            $assign = $this->getFirstItemData($object->getId());
            if ($assign) {
                $keys = ['item_id', 'item_type_id'];

                foreach ($keys as $key) {
                    $value = isset($assign[$key]) ? $assign[$key] : null;
                    $object->setData($key, $value);
                }
            }
        }
        return parent::_afterLoad($object);
    }

    protected function getFirstItemData($messageId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_askit_item')
        )->where('message_id = :message_id')
        ->where('item_type_id IN (?)', [
            MessageInterface::TYPE_CATALOG_PRODUCT,
            MessageInterface::TYPE_CMS_PAGE,
            MessageInterface::TYPE_CATALOG_CATEGORY
        ])
        ->where('item_id <> 0');
        $binds = [':message_id' => (int) $messageId];
        return $connection->fetchRow($select, $binds);
    }

    /**
     * Perform operations after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && $object->hasItemId() && $object->hasItemTypeId()) {
            $table = $this->getTable('swissup_askit_item');
            $data = [
                'message_id' => (int) $object->getId(),
                'item_id' => (int) $object->getItemId(),
                'item_type_id' => (int) $object->getItemTypeId(),
            ];
            $this->getConnection()->insert($table, $data);
        }

        if ($object->hasAssign()) {
            $assign = $object->getAssign();
            $connection = $this->getConnection();
            if (isset($assign['products'])) {
                $condition = [
                    'message_id = ?' => (int) $object->getId(),
                    'item_type_id = ?' => MessageInterface::TYPE_CATALOG_PRODUCT,
                ];

                $connection->delete($this->getTable('swissup_askit_item'), $condition);

                $assignProducts = explode('&', $assign['products']);
                $assignProducts = array_filter($assignProducts);
                foreach ($assignProducts as $productId) {
                    $data = [
                        'message_id' => (int) $object->getId(),
                        'item_id' => (int) $productId,
                        'item_type_id' => MessageInterface::TYPE_CATALOG_PRODUCT,
                    ];
                    $connection->insert($this->getTable('swissup_askit_item'), $data);
                }
            }
            if (isset($assign['pages'])) {
                $condition = [
                    'message_id = ?' => (int) $object->getId(),
                    'item_type_id = ?' => MessageInterface::TYPE_CMS_PAGE,
                ];

                $connection->delete($this->getTable('swissup_askit_item'), $condition);

                $assignPages = explode('&', $assign['pages']);
                $assignPages = array_filter($assignPages);
                foreach ($assignPages as $pageId) {
                    $data = [
                        'message_id' => (int) $object->getId(),
                        'item_id' => (int) $pageId,
                        'item_type_id' => MessageInterface::TYPE_CMS_PAGE,
                    ];
                    $connection->insert($this->getTable('swissup_askit_item'), $data);
                }
            }

            if (isset($assign['categories'])) {
                $condition = [
                    'message_id = ?' => (int) $object->getId(),
                    'item_type_id = ?' => MessageInterface::TYPE_CATALOG_CATEGORY,
                ];

                $connection->delete($this->getTable('swissup_askit_item'), $condition);

                $assignCategories = explode('&', $assign['categories']);
                $assignCategories = array_filter($assignCategories);
                foreach ($assignCategories as $categoryId) {
                    $data = [
                        'message_id' => (int) $object->getId(),
                        'item_id' => (int) $categoryId,
                        'item_type_id' => MessageInterface::TYPE_CATALOG_CATEGORY,
                    ];
                    $connection->insert($this->getTable('swissup_askit_item'), $data);
                }
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $id = $object->getId();
        if (!empty($id)) {
            $connection = $this->getConnection();
            $connection->delete(
                $this->getTable('swissup_askit_item'),
                ['message_id=?' => $object->getId()]
            );
            $connection->delete(
                $this->getTable('swissup_askit_message'),
                ['parent_id=?' => $object->getId()]
            );
        }
        return parent::_afterDelete($object);
    }

    /**
     * Get  of associated to products
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function getAssignProducts($object)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('swissup_askit_item'),
            ['item_id']
        )->where(
            'message_id = :message_id AND item_type_id=' . MessageInterface::TYPE_CATALOG_PRODUCT
        );
        $bind = ['message_id' => (int) $object->getId()];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * Get  of associated to cms pages
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function getAssignPages($object)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('swissup_askit_item'),
            ['item_id']
        )->where(
            'message_id = :message_id AND item_type_id=' . MessageInterface::TYPE_CMS_PAGE
        );
        $bind = ['message_id' => (int) $object->getId()];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * Get of associated to catalog categories
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function getAssignCategories($object)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('swissup_askit_item'),
            ['item_id']
        )->where(
            'message_id = :message_id AND item_type_id=' . MessageInterface::TYPE_CATALOG_CATEGORY
        );
        $bind = ['message_id' => (int) $object->getId()];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return boolean
     */
    public function isQuestion($object)
    {
        return $object->getParentId() == 0;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return boolean
     */
    public function isAdminAnswer($object)
    {
        if ($this->isQuestion($object) || $object->getCustomerId() != null) {
            return false;
        }
        $email = $object->getEmail();
        $name = $object->getCustomerName();

        $user = $this->userFactory->create()->load($email, 'email');

        return $user->getId() && ($name === $user->getFirstname() . ' ' . $user->getLastname());
    }
}
