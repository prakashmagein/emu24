<?php
namespace Swissup\Askit\Model\Search;

/**
 * Search Customer Model
 *
 * @method Customer setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Customer setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method Customer setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method Customer setResults(array $results)
 * @method array getResults()
 */
class Message extends \Magento\Framework\DataObject
{
    /**
     * Adminhtml url model interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $adminhtmlData = null;

    /**
     * @var \Swissup\Askit\Api\MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $customerViewHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Model\UrlInterface $adminhtmlData
     * @param \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $adminhtmlData,
        \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = []
    ) {
        $this->adminhtmlData = $adminhtmlData;
        $this->messageRepository = $messageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerViewHelper = $customerViewHelper;

        parent::__construct($data);
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $this->searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->searchCriteriaBuilder->setPageSize($this->getLimit());
        $searchFields = ['email', 'text'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue('%' . $this->getQuery() . '%')
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->messageRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $message) {
            if ($message->isQuestion()) {
                $url = $this->adminhtmlData->getUrl('askit/question/edit', ['id' => $message->getId()]);
                $type = __('Askit Question');
            } else {
                $url = $this->adminhtmlData->getUrl('askit/answer/edit', ['id' => $message->getId()]);
                $type = __('Askit Answer');
            }
            $result[] = [
                'id' => 'askit/message/1/' . $message->getId(),
                'type' => $type,
                'name' => $message->getText(),
                'description' => $message->getEmail(),
                'url' => $url,
            ];
        }
        $this->setResults($result);
        return $this;
    }
}
