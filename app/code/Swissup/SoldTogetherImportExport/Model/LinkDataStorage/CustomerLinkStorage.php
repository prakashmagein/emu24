<?php

namespace Swissup\SoldTogetherImportExport\Model\LinkDataStorage;

use Swissup\SoldTogether\Model\Customer as CustomerLinkEntity;
use Swissup\SoldTogether\Model\ResourceModel\Customer as CustomerLinkResource;

class CustomerLinkStorage extends AbstractStorage
{
    /**
     * @param CustomerLinkEntity   $linkEntity
     * @param CustomerLinkResource $resource
     */
    public function __construct(
        CustomerLinkEntity $linkEntity,
        CustomerLinkResource $resource
    ) {
        $this->linkEntity = $linkEntity;
        $this->resource = $resource;
    }
}
