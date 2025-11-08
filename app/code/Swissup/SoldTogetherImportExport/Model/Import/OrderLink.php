<?php

namespace Swissup\SoldTogetherImportExport\Model\Import;

use Swissup\SoldTogetherImportExport\Model\LinkDataMerger\OrderLinkMerger;
use Swissup\SoldTogetherImportExport\Model\LinkDataStorage\OrderLinkStorage;

class OrderLink extends AbstractLink
{
    const ENTITY_CODE = 'soldtogether_order';

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        'product_sku',
        'related_sku',
        'weight',
        'is_admin',
        'promo_rule',
        'promo_value'
    ];

    /**
     * @param OrderLinkMerger  $linkDataMerger
     * @param OrderLinkStorage $linkDataStorage
     * @param Context          $context
     */
    public function __construct(
        OrderLinkMerger $linkDataMerger,
        OrderLinkStorage $linkDataStorage,
        Context $context
    ) {
        parent::__construct($linkDataMerger, $linkDataStorage, $context);
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOnAppendConditions(): array
    {
        return [
            'weight' => new \Laminas\Db\Sql\Expression('weight + VALUES(weight)'),
            'is_admin',
            'data_serialized'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOnReplaceConditions(): array
    {
        return [
            'weight',
            'is_admin',
            'data_serialized'
        ];
    }
}
