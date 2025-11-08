<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Autoplay
{
    /**
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if ($slider->getData('autoplay')) {
            $config->setData('autoplay', [
                'delay' => $slider->getData('autoplay')
             ]);
        } else {
            $config->setData('autoplay', false);
        }
    }
}
