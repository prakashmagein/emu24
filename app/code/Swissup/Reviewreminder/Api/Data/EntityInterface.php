<?php
namespace Swissup\Reviewreminder\Api\Data;

interface EntityInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const ORDER_DATE = 'order_date';
    const CUSTOMER_EMAIL = 'customer_email';
    const STATUS = 'status';
    const HASH = 'hash';

    /**
     * Get entity_id
     *
     * return int
     */
    public function getEntityId();

    /**
     * Get order_id
     *
     * return int
     */
    public function getOrderId();

    /**
     * Get order_date
     *
     * return string
     */
    public function getOrderDate();

    /**
     * Get customer_email
     *
     * return string
     */
    public function getCustomerEmail();

    /**
     * Get status
     *
     * return int
     */
    public function getStatus();

    /**
     * Get hash
     *
     * return string
     */
    public function getHash();

    /**
     * Set entity_id
     *
     * @param int $entityId
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setEntityId($entityId);

    /**
     * Set order_id
     *
     * @param int $orderId
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setOrderId($orderId);

    /**
     * Set order_date
     *
     * @param string $orderDate
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setOrderDate($orderDate);

    /**
     * Set customer_email
     *
     * @param string $customerEmail
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setStatus($status);

    /**
     * Set hash
     *
     * @param string $hash
     * return \Swissup\Reviewreminder\Api\Data\EntityInterface
     */
    public function setHash($hash);
}
