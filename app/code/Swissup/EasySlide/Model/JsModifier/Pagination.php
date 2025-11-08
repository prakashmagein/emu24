<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Pagination
{
    /**
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if (!$slider->getData('pagination')) {
            return;
        }

        $config->setData('pagination', [
            'el' => '.swiper-pagination',
            'clickable' => true,
            'type' => 'bullets'
        ]);
    }
}
