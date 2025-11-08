<?php

namespace Swissup\Easybanner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function getCookieName()
    {
        return $this->getConfigValue('easybanner/general/cookie');
    }

    /**
     * @param  string $path
     * @param  string $scope
     * @return string
     */
    public function getConfigValue($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }
}
