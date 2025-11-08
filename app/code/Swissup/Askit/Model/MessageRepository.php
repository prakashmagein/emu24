<?php
namespace Swissup\Askit\Model;

use Magento\Framework\Api\Search\FilterGroup;
// use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Message repository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageRepository implements \Swissup\Askit\Api\MessageRepositoryInterface
{
    /**
     * @var \Swissup\Askit\Model\ResourceModel\Message
     */
    private $resource;

    /**
     * @var \Swissup\Askit\Model\MessageFactory
     */
    private $messageFactory;

    /**
     * @var \Swissup\Askit\Model\ResourceModel\Message\CollectionFactory
     */
    private $messageCollectionFactory;

    /**
     * @var \Swissup\Askit\Api\Data\MessageSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface|boolean
     */
    private $collectionProcessor = false;

    /**
     *
     * @param \Swissup\Askit\Model\ResourceModel\Message $resource
     * @param \Swissup\Askit\Model\MessageFactory $messageFactory
     * @param \Swissup\Askit\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory
     * @param \Swissup\Askit\Api\Data\MessageSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Swissup\Askit\Model\ResourceModel\Message $resource,
        \Swissup\Askit\Model\MessageFactory $messageFactory,
        \Swissup\Askit\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory,
        \Swissup\Askit\Api\Data\MessageSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->messageFactory = $messageFactory;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $this->getCollectionProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Swissup\Askit\Api\Data\MessageInterface $message)
    {
        try {
            $this->resource->save($message);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $object = $this->create();
        $this->resource->load($object, $id);
        return $object;
    }

    /**
     * @return Message
     */
    public function create()
    {
        return $this->messageFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($messageId)
    {
        $messageModel = $this->create();
        $this->resource->load($messageModel, $messageId);

        if (!$messageModel->getId()) {
            // message does not exist
            throw NoSuchEntityException::singleField('messageId', $messageId);
        }
        return $messageModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->messageCollectionFactory->create();
        $this->joinItemTableIfNeeded(
            $searchCriteria->getFilterGroups(),
            $collection
        );

        if ($this->collectionProcessor) {
            $this->collectionProcessor->process($searchCriteria, $collection);
        } else {
            foreach ($searchCriteria->getFilterGroups() as $group) {
                $this->addFilterGroupToCollection($group, $collection);
            }
        }

        $searchResults->setTotalCount($collection->getSize());

        $messages = [];
        /** @var \Swissup\Askit\Model\Message $messageModel */
        foreach ($collection as $messageModel) {
            $messages[] = $messageModel;
        }
        $searchResults->setItems($messages);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Swissup\Askit\Api\Data\MessageInterface $message)
    {
        try {
            $this->resource->delete($message);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($messageId)
    {
        return $this->delete($this->getById($messageId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated
     * @return CollectionProcessorInterface|boolean
     */
    private function getCollectionProcessor()
    {
        $class = \Magento\Framework\Api\SearchCriteria\CollectionProcessor::class;
        if (!class_exists($class)) {
            return false;
        }
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get($class);
        }
        return $this->collectionProcessor;
    }

    /**
     * Add FilterGroup to the collection
     *
     * @param FilterGroup $filterGroup
     * @param AbstractDb $collection
     * @return void
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        AbstractDb $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function joinItemTableIfNeeded(
        array $filterGroups,
        AbstractDb $collection
    ) {
        $fields = [
            'item_id' => 'i.item_id',
            'item_type_id' => 'i.item_type_id'
        ];
        foreach ($filterGroups as $group) {
            foreach ($group->getFilters() as $filter) {
                if (in_array($filter->getField(), array_keys($fields))) {
                    $collection->join(
                        ['i' => 'swissup_askit_item'],
                        'i.message_id = main_table.id',
                        $fields
                    );

                    return;
                }
            }
        }
    }
}
