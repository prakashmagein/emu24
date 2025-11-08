<?php
declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver\DataProvider;

use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Model\ResourceModel\Question\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Swissup\Askit\Model\Resolver\DataProvider\Message as MessageDataProvider;

/**
 * Askit messages data provider
 */
class Messages implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     *
     * @var array[int]
     */
    private $stores = [];

    /**
     *
     * @var integer
     */
    private $pageSize = 20;

    /**
     *
     * @var integer
     */
    private $currentPage = 1;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var MessageDataProvider
     */
    private $messageDataProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Swissup\Askit\Model\Message\Source\PublicStatuses
     */
    private $publicStatuses;

    /**
     * @param MessageDataProvider $messageDataProvider
     * @param CollectionFactory $collectionFactory
     * @param \Swissup\Askit\Model\Message\Source\PublicStatuses $publicStatuses     *
     */
    public function __construct(
        MessageDataProvider $messageDataProvider,
        CollectionFactory $collectionFactory,
        \Swissup\Askit\Model\Message\Source\PublicStatuses $publicStatuses
    ) {
        $this->messageDataProvider = $messageDataProvider;
        $this->collectionFactory = $collectionFactory;
        $this->publicStatuses = $publicStatuses;
    }

    /**
     *
     * @param array $stores
     * @return Messages
     */
    public function setStores(array $stores)
    {
        $this->stores = $stores;
        return $this;
    }

    /**
     *
     * @param int $pageSize
     * @return Messages
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     *
     * @param int $currentPage
     * @return Messages
     */
    public function setCurrentPage(int $currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     *
     * @param int $customerId
     * @return Messages
     */
    public function setCustomerId(int $customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     *
     * @param int $productId
     * @return Messages
     */
    public function setProductId(int $productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return array
     */
    private function getPublicStatuses()
    {
        $openStatuses = $this->publicStatuses->getOptionArray();
        return array_keys($openStatuses);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $pageSize = $this->pageSize;
        $currentPage = $this->currentPage;

        $collection = $this->collectionFactory->create()
            ->addStatusFilter($this->getPublicStatuses())
//            ->addStoreFilter($stores)
            ->addQuestionFilter(0)
            ->addPrivateFilter()
            ->setCurPage($currentPage)
            ->setPageSize($pageSize)
        ;
        if (count($this->stores) > 0) {
            $stores = $this->stores;
            $collection->addStoreFilter($stores);
        }

        $customerId = $this->customerId;
        if (!empty($customerId)) {
            $collection->addPrivateFilter($customerId);
            $collection->addFieldToFilter(
                ['customer_id', 'status'],
                [$customerId, ['in' => $this->getPublicStatuses()]]
            );
        } else {
            $collection->addPrivateFilter();
            $collection->addStatusFilter($this->getPublicStatuses());
        }

        if ($this->productId) {
            $collection->addProductFilter($this->productId);
        }

        $totalCount = $collection->getSize();
        $totalPages = ceil($totalCount / $pageSize);
        $data = [
            'total_count' => $totalCount,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ]
        ];


        $questions = [];
        foreach ($collection as $message) {
            $dataMessage = $this->getDataArray($message);

            $answers = [];
            if ($message->getParentId() == 0) {
                $answerCollection = $message->getApprovedAnswerCollection();
                foreach ($answerCollection as $answerMessage) {
                    $answers[] = $this->getDataArray($answerMessage);
                }
            }
            $dataMessage['answers'] = $answers;
            $questions[$message->getId()] = $dataMessage;
        }
        $data['questions'] = $questions;

        return $data;
    }

    /**
     *
     * @param  MessageInterface $message
     * @return array
     */
    protected function getDataArray(MessageInterface $message): array
    {
        $data = $this->messageDataProvider
            ->setMessage($message)
            ->getData();

        return $data;
    }
}
