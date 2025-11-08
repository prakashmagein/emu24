<?php
namespace Swissup\Askit\Model;

use Swissup\Askit\Api\Data\ItemInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Item extends \Magento\Framework\Model\AbstractModel implements ItemInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'askit_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'askit_item';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'askit_item';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Askit\Model\ResourceModel\Item::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get id
     *
     * return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get message_id
     *
     * return int
     */
    public function getMessageId()
    {
        return $this->getData(self::MESSAGE_ID);
    }

    /**
     * Get item_id
     *
     * return int
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Get item_id
     *
     * return int
     */
    public function getItemTypeId()
    {
        return $this->getData(self::ITEM_TYPE_ID);
    }

    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set message_id
     *
     * @param int $messageId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setMessageId($messageId)
    {
        return $this->setData(self::ITEM_ID, $messageId);
    }

    /**
     * Set item_id
     *
     * @param int $itemId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Set item type id
     *
     * @param int $itemTypeId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setItemTypeId($itemTypeId)
    {
        return $this->setData(self::ITEM_TYPE_ID, $itemTypeId);
    }
}
