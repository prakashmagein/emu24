<?php

namespace Swissup\Ajaxpro\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const ENABLED = 'ajaxpro/main/enabled';
    const VALIDATION = 'ajaxpro/main/validation';
    const CART_ENABLED = 'ajaxpro/main/cart';
    const CART_HANDLE = 'ajaxpro/main/cartHandle';
    const CART_TYPE = 'ajaxpro/main/cartType';
    const CLOSE_TIMEOUT  = 'ajaxpro/main/modalCloseTimeout';
    const FLOATING_CART_ENABLED = 'ajaxpro/main/floatingCart';
    const PRODUCT_ENABLED = 'ajaxpro/main/product';
    const OVERRIDE_MINICART = 'ajaxpro/main/overrideMinicart';
    const OVERRIDE_GETADDTOCARTURL = 'ajaxpro/main/overrideGetAddToCartUrl';
    const QUICK_VIEW_ENABLED = 'ajaxpro/main/quickView';
    const ANIMATION_ENABLED = 'ajaxpro/main/animation';
    const REDIRECT_TO_CART = 'checkout/cart/redirect_to_cart';

    /**
     *
     * @param  string $path
     * @param  string $scope
     * @return string
     */
    public function getConfig($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

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
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::ENABLED);
    }

    /**
     *
     * @return bool
     */
    public function isForceValidation()
    {
        return $this->isEnabled() && $this->isSetFlag(self::VALIDATION);
    }

    /**
     *
     * @return boolean
     */
    public function isCartViewEnabled()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CART_ENABLED);
    }

    /**
     *
     * @return boolean
     */
    public function isQuickViewEnabled()
    {
        return $this->isEnabled() && $this->isSetFlag(self::QUICK_VIEW_ENABLED);
    }

    /**
     *
     * @return boolean
     */
    public function isAnimationEnabled()
    {
        return $this->isEnabled() && $this->isSetFlag(self::ANIMATION_ENABLED);
    }

    /**
     *
     * @return string
     */
    public function getCartHandle()
    {
        return (string) $this->getConfig(self::CART_HANDLE);
    }

    /**
     *
     * @return string
     */
    public function getCartDialogType()
    {
        return (string) $this->getConfig(self::CART_TYPE);
    }

    /**
     * @return int
     */
    public function getCloseTimeout()
    {
        return (int) $this->getConfig(self::CLOSE_TIMEOUT);
    }

    /**
     *
     * @return boolean
     */
    public function isProductViewEnabled()
    {
        return $this->isEnabled() && $this->isSetFlag(self::PRODUCT_ENABLED);
    }

    /**
     *
     * @return bool
     */
    public function isOverrideMinicart()
    {
        $handles = [
            'ajaxpro_popup_minicart',
            'ajaxpro_popup_checkout_cart_index_fixes'
        ];
        return $this->isEnabled() &&
            $this->isCartViewEnabled() &&
            $this->isSetFlag(self::OVERRIDE_MINICART) &&
            in_array($this->getCartHandle(), $handles);
    }

    /**
     *
     * @return bool
     */
    public function isFloatingCartEnabled()
    {
        return $this->isEnabled() && $this->isSetFlag(self::FLOATING_CART_ENABLED);
    }

    /**
     *
     * @return bool
     */
    public function isRedirectToCartEnabled()
    {
        return $this->isSetFlag(self::REDIRECT_TO_CART);
    }

    /**
     * @return bool
     */
    public function isOverrideGetAddToCartUrl()
    {
        return $this->isSetFlag(self::OVERRIDE_GETADDTOCARTURL);
    }
}
