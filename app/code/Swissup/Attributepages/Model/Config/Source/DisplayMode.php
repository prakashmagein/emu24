<?php

namespace Swissup\Attributepages\Model\Config\Source;

use Swissup\Attributepages\Model\Entity as AttributepagesEntity;

class DisplayMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => AttributepagesEntity::DISPLAY_MODE_MIXED, 'label' => __('Description and children')],
            ['value' => AttributepagesEntity::DISPLAY_MODE_DESCRIPTION, 'label' => __('Description only')],
            ['value' => AttributepagesEntity::DISPLAY_MODE_CHILDREN, 'label' => __('Children only')],
        ];
    }
}
