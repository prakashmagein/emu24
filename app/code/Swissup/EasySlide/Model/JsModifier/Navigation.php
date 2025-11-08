<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Navigation
{
    /**
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if (!$slider->getData('navigation')) {
            return;
        }

        $config->setData('navigation', [
            'nextEl' => '.swiper-button-next',
            'prevEl' => '.swiper-button-prev'
        ]);
    }
}
