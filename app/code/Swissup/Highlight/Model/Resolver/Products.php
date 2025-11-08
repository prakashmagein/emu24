<?php
declare(strict_types=1);

namespace Swissup\Highlight\Model\Resolver;

//use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;

use Swissup\Highlight\Model\Resolver\DataProvider\Products as DataProvider;
use Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Products resolver, used for GraphQL request processing.
 * partial inspired Magento\CatalogGraphQl\Model\Resolver\Products
 */
class Products implements ResolverInterface
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var \Swissup\Highlight\Model\Config\Source\Sortorder
     */
    private $sortOrder;

    /**
     * @param DataProvider $dataProvider
     */
    public function __construct(
        DataProvider $dataProvider,
        \Swissup\Highlight\Model\Config\Source\Sortorder $sortOrder
    ) {
        $this->dataProvider = $dataProvider;
        $this->sortOrder = $sortOrder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $types = [
            CollectionFactory::TYPE_DEFAULT,
            CollectionFactory::TYPE_BESTSELLERS,
            CollectionFactory::TYPE_POPULAR,
            CollectionFactory::TYPE_ONSALE,
        ];
        if (!isset($args['collectionType']) || !in_array($args['collectionType'], $types)) {
            throw new GraphQlInputException(__('collectionType is required.'));
        }

//        if (!isset($args['categoryIds']) ) {
//            throw new GraphQlInputException(__('category ids is required.'));
//        }

        $sortOrders = array_keys($this->sortOrder->toArray());
        $sortOrders[] = 'rand';
        $sortOrders[] = 'popularity';
        if (!isset($args['order']) || !in_array($args['order'], $sortOrders) ) {
            throw new GraphQlInputException(__('order is required.'));
        }

        $dirs = [\Magento\Framework\DB\Select::SQL_DESC, \Magento\Framework\DB\Select::SQL_ASC ];
        if (!isset($args['dir']) || !in_array($args['dir'], $dirs)) {
            throw new GraphQlInputException(__('dir is required.'));
        }

        try {
            $provider = $this->dataProvider;
            if (isset($args['pageSize'])) {
                $provider->setPageSize((int) $args['pageSize']);
            }

            if (isset($args['currentPage'])) {
                $provider->setCurrentPage((int) $args['currentPage']);
            }
            $collectionType = (string) $args['collectionType'];
            $provider->setCollectionType($collectionType);
            if ($collectionType === CollectionFactory::TYPE_ONSALE) {
                $provider->addAttributeFilter('special_price', array('gt' => 0));
            }
            $provider->setOrder((string) $args['order']);
            $provider->setDir((string) $args['dir']);

            if (isset($args['conditions']) && !empty($args['conditions'])) {
                $provider->setEncodedConditions((string) $args['conditions']);
            }

            if (isset($args['period']) && !empty($args['period'])) {
                $provider->setPeriod((string) $args['period']);
            }

//            $provider->setCurrentProductId($args['productId']);

//            $storeId = $this->storeManager->getStore()->getId();
//            $isShowOnlySimple = $args['resourceType'] === 'order' || !$this->isSetFlag($args['resourceType'], 'options', $storeId);
//            $provider->setShowOnlySimple($isShowOnlySimple);
//            $provider->setShowOutOfStock($this->isSetFlag($args['resourceType'], 'out', $storeId));
//            $provider->setCanUseRandom($this->isSetFlag($args['resourceType'], 'random', $storeId));

            $data = $provider->getData();

//        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $data;
    }
}
