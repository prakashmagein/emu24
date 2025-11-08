<?php

namespace Swissup\Navigationpro\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_TOPMENU = 'navigationpro/top/identifier';

    /**
     * Retrieve menu identifier for top menu
     *
     * @return string
     */
    public function getTopmenuIdentifier()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_TOPMENU,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
