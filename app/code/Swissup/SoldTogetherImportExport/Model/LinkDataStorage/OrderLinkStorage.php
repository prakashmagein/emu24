<?php

namespace Swissup\SoldTogetherImportExport\Model\LinkDataStorage;

use Swissup\SoldTogether\Model\Order as OrderLinkEntity;
use Swissup\SoldTogether\Model\ResourceModel\Order as OrderLinkResource;

class OrderLinkStorage extends AbstractStorage
{
    /**
     * @param OrderLinkEntity   $linkEntity
     * @param OrderLinkResource $resource
     */
    public function __construct(
        OrderLinkEntity $linkEntity,
        OrderLinkResource $resource
    ) {
        $this->linkEntity = $linkEntity;
        $this->resource = $resource;
    }
}
