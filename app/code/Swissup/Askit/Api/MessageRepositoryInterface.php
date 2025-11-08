<?php

namespace Swissup\Askit\Api;

/**
 * Message CRUD interface.
 * @api
 */
interface MessageRepositoryInterface
{
    /**
     * Create or update a message.
     *
     * @param \Swissup\Askit\Api\Data\MessageInterface $message
     * @return \Swissup\Askit\Api\Data\MessageInterface
     */
    public function save(\Swissup\Askit\Api\Data\MessageInterface $message);

    /**
     * Retrieve message.
     *
     * @param int|null $id
     * @return \Swissup\Askit\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If message with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Get message by message ID.
     *
     * @param int $messageId
     * @return \Swissup\Askit\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If message with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($messageId);

    /**
     * Retrieve messages which match a specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#messageRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Swissup\Askit\Api\Data\MessageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete message.
     *
     * @param \Swissup\Askit\Api\Data\MessageInterface $message
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Swissup\Askit\Api\Data\MessageInterface $message);

    /**
     * Delete message by ID.
     *
     * @param int $messageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($messageId);
}
