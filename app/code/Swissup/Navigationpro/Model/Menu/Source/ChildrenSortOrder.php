<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

class ChildrenSortOrder implements \Magento\Framework\Data\OptionSourceInterface
{
    const DEFAULT = '';
    const ALPHA = 'alpha';

    public function toOptionArray()
    {
        return [
            ['value' => self::DEFAULT, 'label' => __('Default')],
            ['value' => self::ALPHA, 'label' => __('Alphabetical')],
        ];
    }
}
