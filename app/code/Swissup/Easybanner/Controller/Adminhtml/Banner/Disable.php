<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

class Disable extends Enable
{
    /**
     * @var string
     */
    protected $msgSuccess = 'Banner "%1" was disabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 0;
}
