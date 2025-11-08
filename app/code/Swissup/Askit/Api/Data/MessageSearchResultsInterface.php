<?php

namespace Swissup\Askit\Api\Data;

/**
 * Interface for message search results.
 * @api
 */
interface MessageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get messages list.
     *
     * @return \Swissup\Askit\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * Set messages list.
     *
     * @param \Swissup\Askit\Api\Data\MessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
