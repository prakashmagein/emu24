<?php

namespace Swissup\Navigationpro\Model\Config\Source;

class MenuOnly extends Menu
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        // remove "No" option
        unset($options[0]);

        return array_values($options);
    }
}
