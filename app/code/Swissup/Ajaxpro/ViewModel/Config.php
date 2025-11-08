<?php

namespace Swissup\Ajaxpro\ViewModel;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config extends DataObject implements ArgumentInterface
{
    /**
     *
     * @var \Swissup\Ajaxpro\Helper\Data
     */
    private $configHelper;

    /**
     * @param \Swissup\Ajaxpro\Helper\Data $configHelper
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Swissup\Ajaxpro\Helper\Data $configHelper)
    {
        parent::__construct();

        $this->configHelper = $configHelper;
    }

    /**
     * @return bool
     */
    public function isFloatingCartEnabled()
    {
        return $this->configHelper->isFloatingCartEnabled();
    }

    /**
     * @return string
     */
    public function getCartPopupClassName()
    {
        return $this->configHelper->getCartPopupClassName();
    }

    /**
     * @return string
     */
    public function getCartDialogType()
    {
        return $this->configHelper->getCartDialogType();
    }

    /**
     * @return int
     */
    public function getCloseTimeout()
    {
        return $this->configHelper->getCloseTimeout();
    }

    /**
     *
     * @return bool
     */
    public function isForceValidation()
    {
        return $this->configHelper->isForceValidation();
    }

    /**
     *
     * @return bool
     */
    public function isRedirectToCartEnabled()
    {
        return $this->configHelper->isRedirectToCartEnabled();
    }

    /**
     *
     * @return bool
     */
    public function isQuickViewEnabled()
    {
        return $this->configHelper->isQuickViewEnabled();
    }

    /**
     *
     * @return bool
     */
    public function isAnimationEnabled()
    {
        return $this->configHelper->isAnimationEnabled();
    }

    /**
     *
     * @return bool
     */
    public function isProductViewEnabled()
    {
        return $this->configHelper->isProductViewEnabled();
    }

    /**
     *
     * @return bool
     */
    public function isCartViewEnabled()
    {
        return $this->configHelper->isCartViewEnabled();
    }

    /**
     *
     * @return bool
     */
    public function isOverrideMinicart()
    {
        return $this->configHelper->isOverrideMinicart();
    }
}
