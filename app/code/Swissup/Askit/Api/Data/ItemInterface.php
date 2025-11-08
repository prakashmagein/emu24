<?php
namespace Swissup\Askit\Api\Data;

interface ItemInterface
{
    const ID           = 'id';
    const MESSAGE_ID   = 'message_id';
    const ITEM_ID      = 'item_id';
    const ITEM_TYPE_ID = 'item_type_id';

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
     * Get item_id
     *
     * return int
     */
    public function getItemId();

    /**
     * Get item_id
     *
     * return int
     */
    public function getItemTypeId();

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
     * Set item_id
     *
     * @param int $itemId
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setItemId($itemId);

    /**
     * Set item_type_id
     *
     * @param int $itemTypeId
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setItemTypeId($itemTypeId);
}
