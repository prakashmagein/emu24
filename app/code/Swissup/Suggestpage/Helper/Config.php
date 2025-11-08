<?php
namespace Swissup\Suggestpage\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const SHOW_AFTER_ADDTOCART = 'suggestpage/general/show_after_addtocart';
    const AJAXPRO_CART         = 'ajaxpro/main/cart';
    const AJAXPRO_CART_HANDLE  = 'ajaxpro/main/cartHandle';

    /**
     *
     * @param  string $path
     * @param  string $scope
     * @return boolean
     */
    public function isSetFlag($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag($path, $scope);
    }

    /**
     *
     * @param  string $path
     * @param  string $scope
     * @return mixed (string)
     */
    protected function getConfig($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     *
     * @return boolean
     */
    public function isShowAfterAddToCart()
    {
        return $this->isSetFlag(self::SHOW_AFTER_ADDTOCART) ||
            (
                $this->isSetFlag(self::AJAXPRO_CART) &&
                $this->getConfig(self::AJAXPRO_CART_HANDLE) === 'ajaxpro_popup_suggestpage_view'
            );
    }
}
