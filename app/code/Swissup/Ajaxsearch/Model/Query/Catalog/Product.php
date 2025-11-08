<?php
namespace Swissup\Ajaxsearch\Model\Query\Catalog;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Search\Model\SearchCollectionFactory as CollectionFactory;
use Swissup\Ajaxsearch\Model\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\ResourceModel\AbstractResource;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Swissup\Ajaxsearch\Model\Layer\SearchFactory;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Helper\Product\ProductList;

/**
 * Search query model
 */
class Product extends \Swissup\Ajaxsearch\Model\Query
{
    /**
     * Search layer (see di.xml)
     *
     * @var SearchFactory
     */
    protected $searchLayerFactory;

    /**
     * Product Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var Toolbar
     */
    protected $toolbar;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param CollectionFactory $searchCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigHelper $configHelper
     * @param SearchFactory $searchLayerFactory
     * @param Toolbar $toolbar
     * @param AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        QueryCollectionFactory $queryCollectionFactory,
        CollectionFactory $searchCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ConfigHelper $configHelper,
        SearchFactory $searchLayerFactory,
        Toolbar $toolbar,
        ?AbstractResource $resource = null,
        ?DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->searchLayerFactory = $searchLayerFactory;
        $this->toolbar = $toolbar;

        $availableOrders = $this->toolbar->getAvailableOrders();
//        unset($availableOrders['position']);
        $availableOrders = ['relevance' => 'Relevance'] + $availableOrders;

        $this->toolbar->setAvailableOrders(
            $availableOrders
        )->setDefaultDirection(
            'DESC'//ProductList::DEFAULT_SORT_DIRECTION
        )->setDefaultOrder(
            'relevance'
        );

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
     * Retrieve loaded category collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getSuggestCollection()
    {
        if ($this->productCollection === null) {
            /** @var \Swissup\Ajaxsearch\Model\Layer\Search $layer */
            $layer = $this->getLayer();

            $categoryId = $this->getQueryCategoryId();
            if (!empty($categoryId)) {
                $layer->setCurrentCategory($categoryId);
            }

            $this->productCollection = $layer->getProductCollection();

//            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());
            // toolbar setcollection emulation
            $limit = $this->configHelper->getProductLimit();
            if ($limit) {
                // maybe need create self product collection with public method get searchCriteriaBuilder
                // see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::_renderFiltersBefore
                // see Magento\Framework\Search\Adapter\Mysql\Adapter::query
                // if (method_exists($this->productCollection, 'setSearchCriteriaBuilder')) {
                //     $this->searchCriteriaBuilder = $this->productCollection
                //         ->getSearchCriteriaBuilder();
                //     $this->searchCriteriaBuilder
                //         ->setCurrentPage(0)
                //         ->setPageSize($limit)
                //         ;
                //     $this->productCollection
                //         ->setSearchCriteriaBuilder($this->searchCriteriaBuilder);
                // }

                $this->productCollection
                    ->setCurPage(1)
                    ->setPageSize($limit);
            }

            if (!empty($categoryId)) {
                $currentCategory = $layer->getCurrentCategory();
                if ($currentCategory) {
                    $categoryIds = $currentCategory->getAllChildren(true);
                    $this->productCollection->addCategoriesFilter(['in' => $categoryIds]);
                }
            }

            if ($this->toolbar->getCurrentOrder()) {
                $this->productCollection->setOrder(
                    $this->toolbar->getCurrentOrder(),
                    $this->toolbar->getCurrentDirection()
                );
            }
            $this->productCollection->setOrder('entity_id');

//            Magento\CatalogSearch\Model\Layer\Search\Plugin\CollectionFilter::afterFilter
//            $collection->addSearchFilter($query->getQueryText());
//            $this->productCollection->addSearchFilter($queryText);
            $this->productCollection->load(); // or loadWithFilter light load without custum eav attrs
        }

        return $this->productCollection;
    }

    /**
     * Get catalog layer model
     *
     * @return \Swissup\Ajaxsearch\Model\Layer\Search
     */
    private function getLayer()
    {
        return $this->searchLayerFactory->create();
    }
}
