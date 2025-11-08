<?php
declare(strict_types=1);

namespace Swissup\SoldTogether\Model\Resolver\DataProvider;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Swissup\SoldTogether\Model\Resolver\ResourceModels as ResourceModelProvider;

class Products
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $productIds = false;

    /**
     * @var integer
     */
    private $currentProductId;

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
     * @var array
     */
    private $allowedTypes = [];

    /**
     * @var bool
     */
    private $showOutOfStock = false;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @var string
     */
    private $resourceType = 'order';

    /**
     * @var bool
     */
    private $canUseRandom = false;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterface|bool
     */
    private $currentProduct = false;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var ResourceModelProvider
     */
    private $resourceModelProvider;

    /**
     * @param Session                     $checkoutSession
     * @param ProductCollectionFactory    $collectionFactory
     * @param ResourceModelProvider       $resourceModelProvider
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface  $productRepository
     * @param EventManagerInterface       $eventManager
     */
    public function __construct(
        Session $checkoutSession,
        ProductCollectionFactory $collectionFactory,
        ResourceModelProvider $resourceModelProvider,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        EventManagerInterface $eventManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModelProvider = $resourceModelProvider;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->eventManager = $eventManager;
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
     * @param int $productId
     * @return $this
     */
    public function setCurrentProductId($productId)
    {
        $this->currentProductId = $productId;
        return $this;
    }

    /**
     * @return ProductInterface
     */
    private function getProduct()
    {
        if ($this->currentProduct === false && isset($this->currentProductId)) {
            $this->currentProduct = $this->productRepository->getById($this->currentProductId);
        }

        return $this->currentProduct;
    }

    /**
     * Get product ids that will be used to retrieve related products collection
     *
     * @return array
     */
    public function getProductIds()
    {
        if ($this->productIds !== false) {
            return $this->productIds;
        }
        $ids = [];

        if ($this->getProduct()) {
            $ids[] = $this->getProduct()->getId();
        } elseif ($this->checkoutSession->getLastRealOrder()) {
            $items = $this->checkoutSession->getLastRealOrder()->getAllVisibleItems();
            foreach ($items as $item) {
                $ids[] = $item->getProductId();
            }
        } else {
            $items = $this->checkoutSession->getQuote()->getAllItems();
            foreach ($items as $item) {
                $ids[] = $item->getProductId();
            }
        }
        $this->productIds = $ids;

        return $ids;
    }

    /**
     * @param array $types
     * @return $this
     */
    public function setAllowedTypes(array $types)
    {
        $this->allowedTypes = $types;
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     * @deprecated 1.9.5
     */
    public function setShowOnlySimple($status = true)
    {
        if ($status) {
            $this->setAllowedTypes(['simple']);
        } else {
            $this->setAllowedTypes([]);
        }

        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setShowOutOfStock($status = true)
    {
        $this->showOutOfStock = (boolean) $status;
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setCanUseRandom($status = true)
    {
        $this->canUseRandom = (boolean) $status;
        return $this;
    }

    /**
     * @return bool
     */
    private function canUseRandom()
    {
        return $this->canUseRandom;
    }

    /**
     * @param int $limit
     * @return $this
     * @deprecated 1.9.5
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setResourceType(string $type)
    {
        $resource = $this->resourceModelProvider->get($type);
        if (!$resource) {
            throw new InputException("Resource type '{$type}' is wrong");
        }

        $this->resourceType = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    private function getCollection()
    {
        $relationIds = $this->getRelationIds();
        $collection = $this->collectionFactory
            ->create($this->allowedTypes, $this->showOutOfStock)
            ->addIdFilter($this->getProductIds(), true) // true - exclude
            ->setPage($this->currentPage, $this->pageSize);
        $this->joinRelationsData($collection, $relationIds);

        if ($this->canUseRandom()) {
            $useRandom = false;
            if (empty($relationIds)) {
                // No relations. Use random collection.
                $useRandom = true;
            } elseif ($collection->count() == 0) {
                // There are relations. But collection has no available products.
                $useRandom = true;
            }

            if ($useRandom) {
                $collection = $this->getRandomCollection() ?: $collection;
                $collection->setPage($this->currentPage, $this->pageSize);
            }
        }

        return $collection;
    }

    private function joinRelationsData(
        Collection $collection,
        array $relations
    ): void {
        $linkType = $this->resourceType;
        $fields = ['soldtogether_weight' => 'weight'];
        if ($linkType === 'order') {
            $fields[] = 'data_serialized';
        }

        $resource = $this->resourceModelProvider->get($linkType);
        $collection->joinTable(
            ['soldtogether' => $resource->getMainTable()],
            'related_id = entity_id',
            $fields,
            ['relation_id' => ['in' => $relations]]
        );
        $collection->setOrder('soldtogether_weight');
    }

    private function getRelationIds(): array
    {
        $resource = $this->resourceModelProvider->get($this->resourceType);
        if (!$resource) {
            return [];
        }

        $ids = $this->getProductIds();
        $linkedData = $resource->readLinkedData(
            $ids,
            ['relation_id', 'product_id', 'related_id', 'weight']
        );

        $uniqRelations = [];
        foreach ($linkedData as $row) {
            $productId = $row['product_id'];
            $relatedId = $row['related_id'];
            if (in_array($relatedId, $ids)) {
                // skip when related is among products we suggest
                continue;
            }
            $key = $relatedId;
            $uniqRelations[$key] = $row['relation_id'];
        }

        return array_values($uniqRelations);
    }

    /**
     * Prepare random collection of products from same category
     *
     * @return Collection|false
     */
    protected function getRandomCollection()
    {
        $product = $this->getProduct();

        if (!$product) {
            return false;
        }

        if ($product->hasCategory()) {
            /** @var \Magento\Catalog\Model\Category */
            $category = $product->getCategory();
        } elseif ($product->hasCategoryIds()) {
            $categoryIds = $product->getCategoryIds();
            try {
                $category = $this->categoryRepository->get(reset($categoryIds));
            } catch (NoSuchEntityException $e) {
                return false;
            }
        } else {
            return false;
        }

        $collection = $this->collectionFactory
            ->create($this->allowedTypes, $this->showOutOfStock)
            ->addIdFilter($this->getProductIds(), true) // true - exclude
            ->addCategoryFilter($category)
            ->setFlag('is_random', true);
        $collection->getSelect()->order('rand()');

        return $collection;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $pageSize = $collection->getPageSize();

        /**
         * This event used by SEO Images module and others.
         * It improves page rendering time.
         */
        $this->eventManager->dispatch(
            'catalog_block_product_list_collection', [
                'collection' => $collection
            ]
        );

        if ($collection->getFlag('is_random')) {
            // Random collection has only one page
            $totalCount = $collection->count();
        } else {
            $totalCount = $collection->getSize();
        }

        $totalPages = ceil($totalCount / $pageSize);
        $items = [];
        foreach ($collection as $itemObject) {
//            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(
//                new \Magento\Framework\Phrase(
//                    $itemObject->getId()
//    //                (string)$collection->getSelect()
//    ////                $this->currentProductId . $this->getProduct()->getId()
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
                'current_page' => $collection->getCurPage(),
                'total_pages' => $totalPages,
            ]
        ];

        return $data;
    }
}
