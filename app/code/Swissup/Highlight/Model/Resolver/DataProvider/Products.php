<?php
declare(strict_types=1);

namespace Swissup\Highlight\Model\Resolver\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

class Products
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogVisibility;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @var \Swissup\Highlight\Model\Resolver\DataProvider\Conditions
     */
    private $conditions;

    /**
     * @var string
     */
    private $collectionType = \Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory::TYPE_DEFAULT;

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
     * @var string
     */
    private $order = 'position';

    /**
     * @var string
     */
    private $dir = \Magento\Framework\DB\Select::SQL_ASC;

    /**
     * @var string
     */
    private $encodedConditions = null;

    /**
     * @var string
     * @link https://php.net/manual/en/dateinterval.construct.php
     */
    private $period = 'P1M'; // 1 month

    /**
     * @var int|bool
     */
    private $minPopularity = 1;

    /**
     * @var int|bool
     */
    private $maxPopularity = false;

    /**
     * @var array
     */
    private $attributeCodes = [];

    /**
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogVisibility
     * @param \Magento\Catalog\Model\Config                                  $catalogConfig
     * @param \Magento\Framework\Event\ManagerInterface                      $eventManager
     * @param \Swissup\Highlight\Model\Resolver\DataProvider\Conditions      $conditions
     */
    public function __construct(
        \Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $catalogVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Swissup\Highlight\Model\Resolver\DataProvider\Conditions $conditions
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->catalogVisibility = $catalogVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->_eventManager = $eventManager;
        $this->conditions = $conditions;
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
     * @return int
     */
    private function getPageSize()
    {
        return (int) $this->pageSize;
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
     * @return int
     */
    private function getCurrentPage()
    {
        return (int) $this->currentPage;
    }

    /**
     * @param string $order
     * @return $this
     */
    public function setOrder(string $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDir(string $dir)
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setCollectionType(string $type)
    {
        $this->collectionType = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductCollectionType()
    {
        return $this->collectionType;
    }

    /**
     * @param string $conditions
     * @return $this
     */
    public function setEncodedConditions(string $conditions)
    {
        $this->encodedConditions = $conditions;
        return $this;
    }

    /**
     * @param string $period
     * @return $this
     */
    public function setPeriod(string $period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param $popularity
     * @return $this
     */
    public function setMinPopularity($popularity)
    {
        $this->minPopularity = (int) $popularity;
        return $this;
    }

    /**
     * @param $popularity
     * @return $this
     */
    public function setMaxPopularity($popularity)
    {
        $this->maxPopularity = (int) $popularity;
        return $this;
    }

    /**
     * @param string $attributeCode
     * @param mixed $condition
     * @return $this
     */
    public function addAttributeFilter(string $attributeCode, $condition = 1)
    {
        $this->attributeCodes[$attributeCode] = $condition;
        return $this;
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addUrlRewrite();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function _getProductCollection()
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create(
            $this->getProductCollectionType()
        );

        $collection->setVisibility($this->catalogVisibility->getVisibleInCatalogIds());

        // _addProductAttributesAndPrices
        $collection = $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addUrlRewrite();

        $storeId = $this->storeManager->getStore()->getId();
        $collection = $collection->addStoreFilter($storeId);

        $collection = $this->setToolbarParams($collection);

        //Use this method to apply manual filters, etc
        //prepareProductCollection
        \Magento\Framework\Profiler::start(__METHOD__);

        $this->conditions->setData('conditions_encoded', $this->encodedConditions);
        $this->conditions->attachToCollection($collection);

        if ($collection instanceof \Swissup\Highlight\Model\ResourceModel\Product\AddPopularityFilterToCollectionInterface) {
            $collection
                ->addPopularityFilter($this->minPopularity, $this->maxPopularity)
                ->addPeriodFilter($this->getPeriod())
            ;
        }

        //Yesno (Featered), onSale
        foreach($this->attributeCodes as $attributeCode => $condition) {
            try {
               $collection->addAttributeToFilter($attributeCode, $condition);
            } catch (\Exception $e) {

            }
        }

        \Magento\Framework\Profiler::stop(__METHOD__);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        \Magento\Framework\Profiler::stop(__METHOD__);

        return $collection;
    }

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Framework\Data\Collection
     */
    private function setToolbarParams($collection)
    {
        $skipOrder = false;
        // additional sort order parameter, use it to sort by attribute
        if (strtolower($this->order) === 'popularity') {
            $skipOrder = true;
//            $collection->setOrder('popularity', 'DESC');
            $collection->getSelect()->order('popularity DESC');
        } elseif (strtolower($this->order) === 'position') {
            $skipOrder = true;
            $collection->addAttributeToSort($this->order, $this->dir);
        } elseif (in_array(strtolower($this->order), ['rand()', 'rand', 'random'])) {
            $skipOrder = true;
            $collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));
        } else {
            $collection->setOrder($this->order, $this->dir);
        }

//        if ($skipOrder) {
            $collection->addOrder('entity_id', 'DESC');
//        }

        // Toolbar::setCollection
        $currentPage = $this->getCurrentPage();
        $collection = $collection->setCurPage($currentPage);

        $pageSize = (int) $this->getPageSize();
        if ($pageSize) {
            $collection = $collection->setPageSize($pageSize);
        }

        return $collection;
    }

//    /**
//     * Use this method to apply manual filters, etc
//     *
//     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
//     * @return void
//     */
//    public function prepareProductCollection($collection)
//    {
//        \Magento\Framework\Profiler::start(__METHOD__);
//        $conditions = $this->getConditions();
//        $conditions->collectValidatedAttributes($collection);
//        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
//        \Magento\Framework\Profiler::stop(__METHOD__);
//    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        $collection = $this->_getProductCollection();

        return $collection;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $collection = $this->getCollection();

        $totalCount = $collection->getSize();
        $totalPages = ceil($totalCount / $pageSize);

        $items = [];

        foreach ($collection as $itemObject) {
//            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(
//                new \Magento\Framework\Phrase(
////                    $itemObject->getId()
//                    (string) $collection->getSelect()
////                    $this->currentProductId . $this->getProduct()->getId()
//                )
//            );
            $items[$itemObject->getId()] = $itemObject->getData();
            $items[$itemObject->getId()]['model'] = $itemObject;
        }

        $data = [
            'total_count' => $totalCount,
            'items' => $items,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ]
        ];

        return $data;
    }
}
