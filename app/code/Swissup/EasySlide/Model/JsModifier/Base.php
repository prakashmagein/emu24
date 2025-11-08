<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Base
{
    /**
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        $config->addData([
            'effect' => $slider->getData('effect'),
            'speed' => (int)$slider->getData('speed'),
            'centeredSlides' => !!$slider->getData('centeredSlides'),
            'loop' => !!$slider->getData('loop')
        ]);

        if ($slider->getData('startRandomSlide')) {
            $config->setData('startRandomSlide', (bool)$slider->getData('startRandomSlide'));
        }

        $slidesPerView = (float)$slider->getData('slidesPerView');
        if ($slidesPerView > 1) {
            $config->setData('slidesPerView', $slidesPerView);
        }

        if ($slider->getData('spaceBetween')) {
            $config->setData('spaceBetween', (int)$slider->getData('spaceBetween'));
        }

        if ($slider->getData('freeMode')) {
            $config->setData('freeMode', (bool)$slider->getData('freeMode'));
        }
    }
}
