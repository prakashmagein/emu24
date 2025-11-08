<?php

namespace Swissup\SoldTogether\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\SoldTogether\Model\ResourceModel\CronJob\CollectionFactory as JobCollectionFactory;
use Swissup\SoldTogether\Model\Resolver\ResourceModels as ResourceResolver;

class Cron
{
    const XML_PATH_CREATE_CUSTOMER_RELATION = 'soldtogether/relations/customer_on_order_create';
    const XML_PATH_CREATE_ORDER_RELATION = 'soldtogether/relations/order_on_order_create';
    const XML_PATH_ORDER_DAY_LIMIT = 'soldtogether/relations/order_not_older_than_x_days';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JobCollectionFactory
     */
    private $jobCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ResourceResolver
     */
    private $resourceResolver;

    /**
     * @var null|bool
     */
    private $isCustomerEmailIndexExists = null;

    /**
     * @param ScopeConfigInterface     $scopeConfig
     * @param JobCollectionFactory     $jobCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param ResourceResolver         $resourceResolver
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        JobCollectionFactory $jobCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceResolver $resourceResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceResolver = $resourceResolver;
    }

    public function process()
    {
        $timeStart = microtime(true);
        $isCreateOrderRelation = $this->scopeConfig->isSetFlag(
            self::XML_PATH_CREATE_CUSTOMER_RELATION,
            ScopeInterface::SCOPE_STORE
        );
        $isCreateCustomerRelation = $this->scopeConfig->isSetFlag(
            self::XML_PATH_CREATE_ORDER_RELATION,
            ScopeInterface::SCOPE_STORE
        );

        do {
            $orderLinks = [];
            $customerLinks = [];
            $jobCollection = $this->jobCollectionFactory->create();
            $jobCollection->setPageSize(5);
            $jobCount = $jobCollection->count();

            foreach ($jobCollection as $job) {
                $job->getResource()->unserializeFields($job);
                $orderId = $job->getData('data/order_id');
                if (!$orderId) {
                    continue;
                }

                try {
                    $order = $this->orderRepository->get($orderId);
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                if ($isCreateOrderRelation) {
                    $this->collectOrderLinks($order, $orderLinks);
                }

                if ($isCreateCustomerRelation) {
                    $this->collectCustomerLinks($order, $customerLinks);
                }
            }

            if ($orderLinks) {
                $resource = $this->resourceResolver->get('order');
                if ($resource) {
                    $resource->updateLinkedData($orderLinks);
                }
            }

            if ($customerLinks) {
                $resource = $this->resourceResolver->get('customer');
                if ($resource) {
                    $resource->updateLinkedData($customerLinks);
                }
            }

            $jobCollection->walk('delete');
            $timeEnd = microtime(true);
        } while (
            (($timeEnd - $timeStart) < 5) // Stop when execution time greater 5 second
            && ($jobCount > 0)
        );
    }

    public function collectOrderLinks(
        OrderInterface $order,
        array &$links
    ) {
        $visibleItems = $order->getAllVisibleItems();
        if (count($visibleItems) < 2) {
            return;
        }

        $productIds = array_map(function ($item) {
            $product = $item->getProduct();

            return $product ? $product->getId() : null;
        }, $visibleItems);

        $productIds = array_filter($productIds);
        foreach ($productIds as $idA) {
            foreach ($productIds as $idB) {
                if ($idA != $idB) {
                    $this->addLinkItem($idA, $idB, $links);
                }
            }
        }
    }

    public function collectCustomerLinks(
        OrderInterface $order,
        array &$links
    ) {

        $this->checkSalesOrderTableIndex();
        $dayLimit = (int) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_DAY_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
        $createdAtFrom = date('Y-m-d h:i:s', strtotime("-{$dayLimit} day"));
        $createdAtTo = $order->getCreatedAt();
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('customer_email', $order->getCustomerEmail(), 'eq')
            ->addFilter('entity_id', $order->getId(), 'nin')
            ->addFilter('created_at', $createdAtFrom, 'gteq')
            ->addFilter('created_at', $createdAtTo, 'lteq')
            ->create();
        $oldOrders = $this->orderRepository->getList($criteria)->getItems();

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (!$product) {
                continue;
            }

            $idA = $product->getId();
            foreach ($oldOrders as $oldOrder) {
                foreach ($oldOrder->getAllVisibleItems() as $oldItem) {
                    $product = $oldItem->getProduct();
                    if (!$product) {
                        continue;
                    }

                    $idB = $product->getId();
                    if ($idA != $idB) {
                        $this->addLinkItem($idA, $idB, $links);
                        $this->addLinkItem($idB, $idA, $links);
                    }
                }
            }
        }
    }

    /**
     * Check if sales_order table has index for field `customer_email`.
     * If not then add it.
     *
     * @return void
     */
    private function checkSalesOrderTableIndex()
    {
        $indexName = 'SALES_ORDER_CUSTOMER_EMAIL';
        $resource = $this->resourceResolver->get('customer');
        $connection = $resource->getConnection();

        if ($this->isCustomerEmailIndexExists === null) {
            $indexList = $connection->getIndexList(
                $resource->getTable('sales_order')
            );
            $this->isCustomerEmailIndexExists = isset($indexList[$indexName]);
        }

        if ($this->isCustomerEmailIndexExists === false) {
            $connection->addIndex(
                $resource->getTable('sales_order'),
                $indexName,
                'customer_email'
            );
        }
    }

    private function addLinkItem(
        $productId,
        $relatedId,
        array &$links
    ) {
        $key = $productId . '-' . $relatedId;

        if (isset($links[$key])) {
            $links[$key]['weight'] = $links[$key]['weight'] + 1;
        } else {
            $links[$key] = [
                'product_id' => $productId,
                'related_id' => $relatedId,
                'store_id' => '0',
                'weight' => 1,
                'is_admin' => '0'
            ];
        }
    }
}
