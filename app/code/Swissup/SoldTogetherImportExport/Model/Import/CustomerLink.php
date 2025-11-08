<?php

namespace Swissup\SoldTogetherImportExport\Model\Import;

use Swissup\SoldTogetherImportExport\Model\LinkDataMerger\CustomerLinkMerger;
use Swissup\SoldTogetherImportExport\Model\LinkDataStorage\CustomerLinkStorage;

class CustomerLink extends AbstractLink
{
    const ENTITY_CODE = 'soldtogether_customer';

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        'product_sku',
        'related_sku',
        'weight',
        'is_admin'
    ];

    /**
     * @param CustomerLinkMerger  $linkDataMerger
     * @param CustomerLinkStorage $linkDataStorage
     * @param Context             $context
     */
    public function __construct(
        CustomerLinkMerger $linkDataMerger,
        CustomerLinkStorage $linkDataStorage,
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
            'is_admin'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOnReplaceConditions(): array
    {
        return [
            'weight',
            'is_admin'
        ];
    }
}
