<?php

namespace Swissup\SoldTogetherImportExport\Model\Export;

use Swissup\SoldTogether\Model\ResourceModel\Customer as Resource;
use Swissup\SoldTogetherImportExport\Model\Export\CustomerLink\CollectionFactory as EntityCollectionFactory;

class CustomerLink extends AbstractLink
{
    /**
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param Resource                $resource
     * @param Context                 $context
     * @param array                   $data
     */
    public function __construct(
        EntityCollectionFactory $entityCollectionFactory,
        Resource $resource,
        Context $context,
        array $data = []
    ) {
        parent::__construct($entityCollectionFactory, $resource, $context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeCode()
    {
        return 'soldtogether_customer';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCollection()
    {
        $collection = parent::getAttributeCollection();

        return $collection;
    }
}
