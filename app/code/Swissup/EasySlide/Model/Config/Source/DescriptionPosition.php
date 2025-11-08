<?php

namespace Swissup\EasySlide\Model\Config\Source;

class DescriptionPosition implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            "0" => [
                "value" => "top",
                "label" => "top"
            ],
            "1" => [
                "value" => "right",
                "label" => "right"
            ],
            "2" => [
                "value" => "bottom",
                "label" => "bottom"
            ],
            "3" => [
                "value" => "left",
                "label" => "left"
            ]
        ];
    }
}
