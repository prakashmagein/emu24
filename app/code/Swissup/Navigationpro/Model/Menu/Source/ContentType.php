<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ContentType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableTypes = [
            'html'      => 'Widget or Plain Html',
            'children'  => 'Children (Subcategories and Child Items)',
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
