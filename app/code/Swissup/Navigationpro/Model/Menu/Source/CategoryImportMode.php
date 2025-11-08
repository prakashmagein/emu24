<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CategoryImportMode implements OptionSourceInterface
{
    const MODE_ALL      = 'all';
    const MODE_CHILDREN = 'children';
    const MODE_SELECTED = 'selected';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableTypes = [
            '0'                 => 'Select...',
            self::MODE_ALL      => 'Selected Item and its Children',
            self::MODE_CHILDREN => 'Children of the Selected Item',
            self::MODE_SELECTED => 'Selected Item only',
        ];
        $options = [];
        foreach ($availableTypes as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
