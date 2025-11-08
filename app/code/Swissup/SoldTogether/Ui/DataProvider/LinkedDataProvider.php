<?php
namespace Swissup\SoldTogether\Ui\DataProvider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;

class LinkedDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string             $name
     * @param string             $primaryFieldName
     * @param string             $requestFieldName
     * @param AbstractCollection $collection
     * @param array              $meta
     * @param array              $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        AbstractCollection $collection,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->leftJoinNames($collection);
        $this->collection = $collection;
    }

    private function leftJoinNames(AbstractCollection $collection): void
    {
        $resourceProduct = $this->getResourceProduct();
        $linkField = $resourceProduct->getLinkField();
        $entityTable = $collection->getTable('catalog_product_entity');

        $name = $this->getProductAttribute(ProductAttributeInterface::CODE_NAME);
        $attributeTable = $collection->getTable($name->getBackendTable());
        $joinedTables = $collection->getSelect()->getPart(Select::FROM);

        foreach (['product', 'related'] as $entity) {
            if (!isset($joinedTables[$entity])) {
                $collection->getSelect()->joinLeft(
                    [$entity => $entityTable],
                    "{$entity}.entity_id = main_table.{$entity}_id",
                    []
                );
            }

            $entityName = "{$entity}_name";
            if (!isset($joinedTables[$entityName])) {
                $collection->getSelect()->joinLeft(
                    [$entityName => $attributeTable],
                    implode(' AND ',[
                        "{$entityName}.{$linkField} = {$entity}.{$linkField}",
                        "{$entityName}.attribute_id = {$name->getId()}",
                        "{$entityName}.store_id = " . Store::DEFAULT_STORE_ID,
                    ]),
                    [$entityName => "{$entityName}.value"]
                );
            }

            $collection->addFilterToMap($entityName, "{$entityName}.value");
        }
    }

    private function getProductAttribute(string $attributeCode)
    {
        $objectManager = ObjectManager::getInstance();
        $attributeRepo = $objectManager->get(ProductAttributeRepositoryInterface::class);

        return $attributeRepo->get($attributeCode);
    }

    private function getResourceProduct()
    {
        $objectManager = ObjectManager::getInstance();

        return $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
    }
}
