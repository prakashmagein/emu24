<?php
namespace Swissup\Ajaxsearch\Model\Query\Cms;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\ResourceModel\Page\Grid\Collection;
use Magento\Search\Model\SearchCollectionFactory as CollectionFactory;
use Swissup\Ajaxsearch\Model\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\ResourceModel\AbstractResource;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Framework\Api\FilterBuilder;
use Magento\Store\Model\Store;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Search\Model\SearchCollectionFactory;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Search query model
 */
class Page extends \Swissup\Ajaxsearch\Model\Query\WithFilterPool
{
    /**
     *
     * @var string
     */
    protected $name = 'cms_page_listing_data_source';

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @return string
     */
    protected function getName()
    {
        return $this->name;
    }

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
        MetadataPool $metadataPool,
        ?AbstractResource $resource = null,
        ?DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $context,
            $registry,
            $queryCollectionFactory,
            $searchCollectionFactory,
            $storeManager,
            $scopeConfig,
            $configHelper,
            $filterPool,
            $searchCriteriaBuilder,
            $filterBuilder,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    protected function _getSuggestCollection()
    {
        $collection = $this->_queryCollectionFactory
            ->setInstanceName(Collection::class)
            ->create()
            ->addFieldToFilter('is_active', 1)
            ;

        $withDublicateUrl = false;

        if ($withDublicateUrl) {
            $collection->addStoreFilter($this->getStoreId());
        } else {
            $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $select = $collection->getSelect();
            $select->join(
                ['store_table' => $collection->getTable('cms_page_store')],
                'main_table.' . $linkField . ' = store_table.' . $linkField,
                []
            );

            $subSelect = clone $collection->getSelect();

            $subSelect->reset(\Laminas\Db\Sql\Select::COLUMNS);
            $subSelect->columns(['identifier', 'MAX(store_table.store_id) AS maxs']);
            $subSelect->where(
                'store_table.store_id IN (?)',
                [$this->getStoreId(), Store::DEFAULT_STORE_ID]
            );
            $subSelect->group('main_table.identifier');

            $select->join(
                ['temp1' => $subSelect],
                'temp1.identifier = main_table.identifier AND store_table.store_id = temp1.maxs',
                []
            );

            $select->reset(\Laminas\Db\Sql\Select::WHERE);
        }

        $limit = $this->configHelper->getPageLimit();
        if ($limit) {
            $collection->setPageSize($limit);
        }
        /** @var \Magento\Cms\Model\ResourceModel\Page\Grid\Collection $collection */
        $collection = $this->applyFilters($collection);
        return $collection;
    }

    /**
     *
     * @return array
     */
    protected function getFilters()
    {
        $filters = [];
        $value = $this->getQueryText();
        if ($value) {
            $filter = $this->filterBuilder
                ->setConditionType('fulltext')
                ->setField('fulltext')
                ->setValue($value)
                ->create();
            $filters[] = $filter;
        }
        return $filters;
    }
}
