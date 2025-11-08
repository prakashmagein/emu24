<?php

namespace Swissup\SoldTogetherImportExport\Model\Export\Link;

use Magento\Framework\Data\Collection as AttributeCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Export;

abstract class AbstractCollectionFactory
{
    /**
     * Class of collection to create
     *
     * @var string
     */
    protected $collectionClass = '';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param  ObjectManagerInterface $objectManager
     * @return void
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param  AttributeCollection $attributeCollection
     * @param  array               $filters
     * @return AbstractCollection
     */
    public function create(
        AttributeCollection $attributeCollection,
        array $filters
    ): AbstractCollection {
        $collection = $this->objectManager->create($this->collectionClass);
        $this->joinSku($collection);

        foreach ($this->retrieveFilterData($filters) as $columnName => $value) {
            $attributeDefinition = $attributeCollection->getItemById($columnName);
            if (!$attributeDefinition) {
                throw new LocalizedException(__(
                    'Given column name "%columnName" is not present in collection.',
                    ['columnName' => $columnName]
                ));
            }

            $this->addFilter(
                $collection,
                $columnName,
                $attributeDefinition->getData('backend_type'),
                $value
            );
        }

        return $collection;
    }

    /**
     * Add filter to collection
     *
     * @param AbstractCollection $collection
     * @param string             $field
     * @param string             $fieldType
     * @param mixed              $value
     */
    protected function addFilter(
        AbstractCollection $collection,
        string $field,
        string $fieldType,
        $value
    ) {
        if (!$fieldType) {
            throw new LocalizedException(__(
                'There is no backend type specified for column "%columnName".',
                ['columnName' => $field]
            ));
        }

        $condition = [];
        switch ($fieldType) {
            case 'varchar':
                $condition['like'] = "%{$value}%";
                break;

            case 'int' or 'decimal':
                if (is_array($value)) {
                    $from = $value[0] ?? null;
                    $to = $value[1] ?? null;

                    if (is_numeric($from) && !empty($from)) {
                        $condition['from'] = $from;
                    }

                    if (is_numeric($to) && !empty($to)) {
                        $condition['to'] = $to;
                    }
                } else {
                    $condition['eq'] = $value;
                }

                break;
        }

        if (!empty($condition)) {
            $collection->addFieldToFilter($field, $condition);
        }
    }

    /**
     * @param  AbstractCollection $collection
     * @return void
     */
    protected function joinSku(AbstractCollection $collection)
    {
        $productTable = $collection->getTable('catalog_product_entity');

        $collection->join(
            ['product' => $productTable],
            "product.entity_id = main_table.product_id",
            ['product_sku' => 'product.sku']
        );
        $collection->addFilterToMap('product_sku', 'product.sku');

        $collection->join(
            ['related' => $productTable],
            "related.entity_id = main_table.related_id",
            ['related_sku' => 'related.sku']
        );
        $collection->addFilterToMap('related_sku', 'related.sku');
    }

    /**
     * @param array $filters
     * @return array
     */
    private function retrieveFilterData(array $filters)
    {
        return array_filter(
            $filters[Export::FILTER_ELEMENT_GROUP] ?? [],
            function ($value) {
                return $value !== '';
            }
        );
    }
}
