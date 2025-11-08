<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Scrollbar
{
    /**
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if (!$slider->getData('scrollbar')) {
            return;
        }

        $config->setData('scrollbar', [
            'el' => '.swiper-scrollbar'
        ]);
    }
}
