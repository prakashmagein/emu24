<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ContentDisplayMode implements OptionSourceInterface
{
    const MODE_ALWAYS = 'always';
    const MODE_IF_HAS_CHILDREN = 'if_has_children';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $modes = [
            self::MODE_ALWAYS => 'Always',
            self::MODE_IF_HAS_CHILDREN => 'When item has visible children (Subcategories)',
        ];
        $options = [];
        foreach ($modes as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
