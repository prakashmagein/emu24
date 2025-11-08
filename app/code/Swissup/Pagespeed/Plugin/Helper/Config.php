<?php

namespace Swissup\Pagespeed\Plugin\Helper;

use Magento\Framework\App\Request\Http as RequestHttp;

class Config
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(RequestHttp $request)
    {
        $this->request = $request;
    }

    /**
     * Disable Pagespeed is amp param = 1
     *
     * @param  \Swissup\Pagespeed\Helper\Config $subject
     * @return bool
     */
    public function afterIsEnabled(
        \Swissup\Pagespeed\Helper\Config $subject,
        $result
    ) {
        $isAMP = (bool) $this->request->getParam('amp');
        if ($isAMP) {
            return false;
        }

        return $result;
    }
}
