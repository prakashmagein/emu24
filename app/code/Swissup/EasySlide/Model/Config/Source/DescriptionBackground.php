<?php

namespace Swissup\EasySlide\Model\Config\Source;

class DescriptionBackground implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            "0" => ["value" => "light", "label" => "light"],
            "1" => ["value" => "dark", "label" => "dark"],
            "2" => ["value" => "transparent", "label" => "transparent"]
        ];
    }
}
