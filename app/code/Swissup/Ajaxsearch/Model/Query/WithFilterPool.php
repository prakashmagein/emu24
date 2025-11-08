<?php
namespace Swissup\Ajaxsearch\Model\Query;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Search\Model\SearchCollectionFactory;
use Swissup\Ajaxsearch\Model\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\ResourceModel\AbstractResource;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Framework\Api\FilterBuilder;

/**
 * Search query model
 */
abstract class WithFilterPool extends \Swissup\Ajaxsearch\Model\Query
{
    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param SearchCollectionFactory $searchCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigHelper $configHelper
     * @param FilterPool $filterPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        QueryCollectionFactory $queryCollectionFactory,
        SearchCollectionFactory $searchCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ConfigHelper $configHelper,
        FilterPool $filterPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ?AbstractResource $resource = null,
        ?DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->filterPool = $filterPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $context,
            $registry,
            $queryCollectionFactory,
            $searchCollectionFactory,
            $storeManager,
            $scopeConfig,
            $configHelper,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     *
     * @param  DbCollection $collection
     * @param  \Magento\Framework\Api\Search\SearchCriteria $searchCriteria
     * @return DbCollection
     */
    protected function applyFilters($collection, $searchCriteria = null)
    {
        if (null === $searchCriteria) {
            $searchCriteria = $this->getSearchCriteria();
        }
        $this->filterPool->applyFilters($collection, $searchCriteria);
        return $collection;
    }

    /**
     * Get all intercepting filters
     *
     * @return array
     */
    abstract protected function getFilters();

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteria
     */
    protected function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $value = $this->getQueryText();
            if ($value) {
                $filters = $this->getFilters();
                foreach ($filters as $filter) {
                    $this->searchCriteriaBuilder->addFilter($filter);
                }
            }

            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->getName());
        }
        return $this->searchCriteria;
    }
}
