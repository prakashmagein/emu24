<?php

namespace Swissup\EasySlide\Controller\Adminhtml\Slider;

class Disable extends Enable
{
    /**
     * @var string
     */
    protected $msgSuccess = 'Slider "%1" was disabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 0;
}
