<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Fblike\Model\Config\Source;

class Action implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => "like", 'label' => __('Like')],
            ['value' => "recommend", 'label' => __('Recommend')]
        ];
        return $result;
    }
}
