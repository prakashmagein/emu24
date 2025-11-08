<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ColumnsCount implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $i = 1;
        $max = 8;
        $options = [];

        do {
            $options[] = [
                'label' => ' ' . $i, // fixes magento js error
                'value' => $i
            ];
        } while ($i++ < $max);

        return $options;
    }
}
