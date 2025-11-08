<?php

namespace Swissup\Attributepages\Model\Config\Source;

use Swissup\Attributepages\Model\Entity as AttributepagesEntity;

class ListingMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => AttributepagesEntity::LISTING_MODE_IMAGE, 'label' => __('Images')],
            ['value' => AttributepagesEntity::LISTING_MODE_LINK, 'label' => __('Links')],
        ];
    }
}
