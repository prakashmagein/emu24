<?php

namespace Swissup\SoldTogetherImportExport\Model\Export\CustomerLink;

use Swissup\SoldTogether\Model\ResourceModel\Customer\Collection;
use Swissup\SoldTogetherImportExport\Model\Export\Link\AbstractCollectionFactory;

class CollectionFactory extends AbstractCollectionFactory
{
    /**
     * {@inheritdoc}
     */
    protected $collectionClass = Collection::class;
}
