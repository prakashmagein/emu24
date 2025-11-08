<?php
namespace Swissup\Askit\Api\Data;

interface MessageInterface
{
    const ID            = 'id';
    const PARENT_ID     = 'parent_id';
    const STORE_ID      = 'store_id';
    const CUSTOMER_ID   = 'customer_id';
    const CUSTOMER_NAME = 'customer_name';
    const EMAIL         = 'email';
    const TEXT          = 'text';
    const HINT          = 'hint';
    const STATUS        = 'status';
    const CREATED_TIME  = 'created_time';
    const UPDATE_TIME   = 'update_time';
    const IS_PRIVATE    = 'is_private';

    const STATUS_ENABLED     = 1;
    const STATUS_DISABLED    = 0;

    const STATUS_PENDING     = 1;
    const STATUS_APPROVED    = 2;
    const STATUS_DISAPPROVED = 3;
    const STATUS_CLOSE       = 4;

    const TYPE_UNKNOWN          = -1;
    const TYPE_CATALOG_PRODUCT  = 1;
    const TYPE_CATALOG_CATEGORY = 2;
    const TYPE_CMS_PAGE         = 3;


    /**
     * Get id
     *
     * return int
     */
    public function getId();

    /**
     * Get parent_id
     *
     * return int
     */
    public function getParentId();

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId();

    /**
     * Get customer_id
     *
     * return int
     */
    public function getCustomerId();

    /**
     * Get customer_name
     *
     * return string
     */
    public function getCustomerName();

    /**
     * Get email
     *
     * return string
     */
    public function getEmail();

    /**
     * Get text
     *
     * return string
     */
    public function getText();

    /**
     * Get hint
     *
     * return int
     */
    public function getHint();

    /**
     * Get status
     *
     * return int
     */
    public function getStatus();

    /**
     * Get created_time
     *
     * return string
     */
    public function getCreatedTime();

    /**
     * Get update_time
     *
     * return string
     */
    public function getUpdateTime();

    /**
     * Get private
     *
     * return int
     */
    public function getIsPrivate();

    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setId($id);

    /**
     * Set parent_id
     *
     * @param int $parentId
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setParentId($parentId);

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setStoreId($storeId);

    /**
     * Set customer_id
     *
     * @param int $customerId
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set customer_name
     *
     * @param string $customerName
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setCustomerName($customerName);

    /**
     * Set email
     *
     * @param string $email
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setEmail($email);

    /**
     * Set text
     *
     * @param string $text
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setText($text);

    /**
     * Set hint
     *
     * @param int $hint
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setHint($hint);

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setStatus($status);

    /**
     * Set created_time
     *
     * @param string $createdTime
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setCreatedTime($createdTime);

    /**
     * Set update_time
     *
     * @param string $updateTime
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set private
     *
     * @param int $private
     * return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function setIsPrivate($private);

    /**
     *
     *
     * return boolean
     */
    public function isQuestion();
}
