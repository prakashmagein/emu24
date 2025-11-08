<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Lazy
{
    /**
     * Enable lazy loading for slides
     *
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if (!$slider->getData('lazy')) {
            return;
        }

        $config->setData('preloadImages', false);
        $config->setData('autoHeight', false);
        $config->setData('lazy', [
            'loadPrevNext' => !!$slider->getData('loadPrevNext')
        ]);
    }
}
