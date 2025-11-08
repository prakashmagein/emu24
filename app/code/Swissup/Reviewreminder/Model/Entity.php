<?php
namespace Swissup\Reviewreminder\Model;

use Swissup\Reviewreminder\Api\Data\EntityInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Entity extends \Magento\Framework\Model\AbstractModel
    implements EntityInterface, IdentityInterface
{
    // Record statuses
    const STATUS_NEW = 1;
    const STATUS_SENT = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_FAILED = 4;
    const STATUS_PENDING = 5;
    const STATUS_UNSUBSCRIBED = 6;

    // Review statuses
    const NOT_REVIEWED = 1;
    const REVIEWED = 2;
    const NO_CUSTOMER = 3;
    /**
     * cache tag
     */
    const CACHE_TAG = 'reviewreminder_entity';

    /**
     * @var string
     */
    protected $_cacheTag = 'reviewreminder_entity';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'reviewreminder_entity';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Reviewreminder\Model\ResourceModel\Entity');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get entity_id
     *
     * return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get order_id
     *
     * return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get order_date
     *
     * return string
     */
    public function getOrderDate()
    {
        return $this->getData(self::ORDER_DATE);
    }

    /**
     * Get customer_email
     *
     * return string
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * Get status
     *
     * return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get hash
     *
     * return string
     */
    public function getHash()
    {
        return $this->getData(self::HASH);
    }

    /**
     * Set entity_id
     *
     * @param int $entityId
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Set order_id
     *
     * @param int $orderId
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set order_date
     *
     * @param string $orderDate
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setOrderDate($orderDate)
    {
        return $this->setData(self::ORDER_DATE, $orderDate);
    }

    /**
     * Set customer_email
     *
     * @param string $customerEmail
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set hash
     *
     * @param string $hash
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setHash($hash)
    {
        return $this->setData(self::HASH, $hash);
    }

    /**
     * Prepare reminder's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_NEW => __('New'),
            self::STATUS_SENT => __('Sent'),
            self::STATUS_CANCELLED => __('Cancelled'),
            self::STATUS_FAILED => __('Failed'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_UNSUBSCRIBED => __('Unsubscribed')
        ];
    }

    /**
     * Prepare review's statuses.
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        return [
            self::NOT_REVIEWED => __('Not reviewed'),
            self::REVIEWED => __('Reviewed'),
            self::NO_CUSTOMER => __('Customer not found')
        ];
    }

    public function getOrderInfo()
    {
        return $this->getResource()->getOrderInfo($this->getOrderId());
    }
}
