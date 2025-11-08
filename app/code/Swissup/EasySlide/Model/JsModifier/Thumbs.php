<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Thumbs
{
    /**
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if (!$slider->getData('thumbs')) {
            return;
        }

        $config->setData('thumbs', [
            'swiper' => [
                'el' => ".easyslide-swiper-{$slider->getIdentifier()} + .easyslide-thumbs",
                'direction' => in_array($slider->getData('thumbs_position'), ['right', 'left']) ? 'vertical' : 'horizontal',
                'slidesPerView' => 'auto',
                'lazy' => !!$slider->getData('lazy'),
                'watchSlidesVisibility' => true,
                'watchSlidesProgress' => true
            ],
            'autoScrollOffset' => 1
        ]);
    }
}
