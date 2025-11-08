<?php
namespace Swissup\Ajaxsearch\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Search\Model\ResourceModel\Query\Collection as QueryCollection;
use Swissup\Ajaxsearch\Model\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Search\Model\SearchCollectionInterface as Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;

/**
 * Search query model
 */
class Query extends \Magento\Search\Model\Query
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param \Magento\Search\Model\SearchCollectionFactory $searchCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        QueryCollectionFactory $queryCollectionFactory,
        \Magento\Search\Model\SearchCollectionFactory $searchCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ConfigHelper $configHelper,
        ?AbstractResource $resource = null,
        ?DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct(
            $context,
            $registry,
            $queryCollectionFactory,
            $searchCollectionFactory,
            $storeManager,
            $scopeConfig,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Retrieve collection of suggest queries
     *
     * @return QueryCollection
     */
    protected function _getSuggestCollection()
    {
        return $this->_queryCollectionFactory->create()
            ->setStoreId(
                $this->getStoreId()
            )
            ->setQueryFilter(
                $this->getQueryText()
            )
            ;
    }

    /**
     * Retrieve collection of suggest queries
     *
     * @return QueryCollection
     */
    public function getSuggestCollection()
    {
        $collection = $this->getData('suggest_collection');
        if ($collection === null) {
            $collection = $this->_getSuggestCollection();
            $this->setData('suggest_collection', $collection);
        }
        return $collection;
    }
}
