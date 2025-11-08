<?php
declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver\DataProvider;

use Swissup\Askit\Api\Data\MessageInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Askit message data provider
 */
class Message
{
    /**
     *
     * @var MessageInterface
     */
    private $message;

    /**
     * @var FilterEmulate
     */
    protected $widgetFilter;

    /**
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        FilterEmulate $widgetFilter
    ) {
        $this->widgetFilter = $widgetFilter;
    }

    /**
     *
     * @param MessageInterface $message
     * @return Message
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $data = [];
        if ($this->message) {
            $data = $this->getDataArray($this->message);
        }

        return $data;
    }

    /**
     *
     * @param  MessageInterface $message
     * @return array
     */
    protected function getDataArray(MessageInterface $message): array
    {
        $text = $this->widgetFilter->filter(
            $message->getText()
        );

        $data = [
            MessageInterface::ID            => $message->getId(),
            MessageInterface::PARENT_ID     => $message->getParentId(),
            MessageInterface::STORE_ID      => $message->getStoreId(),
            MessageInterface::CUSTOMER_ID   => $message->getCustomerId(),
            MessageInterface::CUSTOMER_NAME => $message->getCustomerName(),
            MessageInterface::EMAIL         => $message->getEmail(),
            MessageInterface::TEXT          => $text,
            MessageInterface::HINT          => $message->getHint(),
            MessageInterface::STATUS        => $message->getStatus(),
            MessageInterface::CREATED_TIME  => $message->getCreatedTime(),
            MessageInterface::UPDATE_TIME   => $message->getUpdateTime(),
            MessageInterface::IS_PRIVATE    => $message->getIsPrivate(),
            'item_type_id'                  => $message->getItemTypeId(),
            'item_id'                       => $message->getItemId(),
        ];

        return $data;
    }
}
