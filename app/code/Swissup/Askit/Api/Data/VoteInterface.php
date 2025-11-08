<?php
namespace Swissup\Askit\Api\Data;

interface VoteInterface
{
    const ID = 'id';
    const MESSAGE_ID = 'message_id';
    const CUSTOMER_ID = 'customer_id';

    /**
     * Get id
     *
     * return int
     */
    public function getId();

    /**
     * Get message_id
     *
     * return int
     */
    public function getMessageId();

    /**
     * Get customer_id
     *
     * return int
     */
    public function getCustomerId();


    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setId($id);

    /**
     * Set message_id
     *
     * @param int $messageId
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setMessageId($messageId);

    /**
     * Set customer_id
     *
     * @param int $customerId
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setCustomerId($customerId);
}
