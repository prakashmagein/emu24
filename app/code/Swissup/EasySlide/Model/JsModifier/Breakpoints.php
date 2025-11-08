<?php

namespace Swissup\EasySlide\Model\JsModifier;

use Magento\Framework\DataObject;
use Swissup\EasySlide\Model\Slider;

class Breakpoints
{
    /**
     * Add breakpoints to config
     *
     * @param  DataObject $config
     * @param  Slider     $slider
     */
    public function modify(DataObject $config, Slider $slider)
    {
        if (!$slider->getData('breakpoints')) {
            return;
        }

        $items = [];
        foreach ($slider->getData('breakpoints') as $breakpoint) {
            $item = array_intersect_key(
                $breakpoint,
                array_flip(['slidesPerView', 'spaceBetween'])
            );
            $items[$breakpoint['screen_width']] = array_filter($item);
        }

        $config->setData('breakpoints', $items);
    }
}
