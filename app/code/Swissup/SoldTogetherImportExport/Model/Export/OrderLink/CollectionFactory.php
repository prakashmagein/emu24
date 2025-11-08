<?php

namespace Swissup\SoldTogetherImportExport\Model\Export\OrderLink;

use Swissup\SoldTogether\Model\ResourceModel\Order\Collection;
use Swissup\SoldTogetherImportExport\Model\Export\Link\AbstractCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CollectionFactory extends AbstractCollectionFactory
{    
    /**
     * {@inheritdoc}
     */
    protected $collectionClass = Collection::class;

    /**
     * {@inheritdoc}
     */
    protected function addFilter(
        AbstractCollection $collection,
        string $field,
        string $fieldType,
        $value
    ) {
        if (in_array($field, ['promo_rule', 'promo_value'])) {
            // promo fileds are parts of data_serialized field
            // use JSON_EXTRACT in DB query to filter data
            $collection->addFilterToMap(
                $field,
                new \Zend_Db_Expr("JSON_EXTRACT(data_serialized, '$.{$field}')")
            );
        }

        parent::addFilter($collection, $field, $fieldType, $value);
    }
}
