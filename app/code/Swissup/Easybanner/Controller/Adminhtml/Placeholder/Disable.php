<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

class Disable extends Enable
{
    /**
     * @var string
     */
    protected $msgSuccess = 'Placeholder "%1" was disabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 0;
}
