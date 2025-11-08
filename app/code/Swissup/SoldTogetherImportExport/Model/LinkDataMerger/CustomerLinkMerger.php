<?php

namespace Swissup\SoldTogetherImportExport\Model\LinkDataMerger;

class CustomerLinkMerger extends AbstractMerger
{
    public function __construct()
    {
        parent::__construct([
            'relation_id',
            'product_id',
            'related_id',
            'weight',
            'is_admin'
        ]);
    }
}
