<?php

namespace Swissup\SoldTogetherImportExport\Model\LinkDataMerger;

class OrderLinkMerger extends AbstractMerger
{
    public function __construct()
    {
        parent::__construct([
            'relation_id',
            'product_id',
            'related_id',
            'weight',
            'is_admin',
            ['promo_rule' => 'data_serialized/promo_rule'],
            ['promo_value' => 'data_serialized/promo_value']
        ]);
    }
}
