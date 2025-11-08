<?php

namespace Swissup\Ajaxpro\Block\Cart;

class Sidebar extends \Magento\Checkout\Block\Cart\Sidebar
{
    protected $_template = 'Swissup_Ajaxpro::checkout.cart/floatingcart.phtml';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $imageHelper,
            $jsLayoutDataProvider,
            $data,
            $serializer
        );

        // $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
        //     ->get(\Magento\Framework\Serialize\Serializer\Json::class);

        $liteConfig = $this->getConfig();
        $components = ['ajaxpro_floatingcart_content', 'ajaxpro_minicart_content'];
        foreach ($components as $componentName) {
            if (isset($this->jsLayout['components']) &&
                isset($this->jsLayout['components'][$componentName]) &&
                isset($this->jsLayout['components'][$componentName]['config'])
            ) {
                $this->jsLayout['components'][$componentName]['config']['checkout_lite_config'] = $liteConfig;
            }
        }
    }

    /**
     * Get serialized config
     *
     * @return string
     * @since 100.2.0
     */
    // public function getSerializedLiteConfig()
    // {
    //     $config = $this->getConfig();
    //     return $this->serializer ?
    //         $this->serializer->serialize($config) : \Zend_Json::encode($config);
    // }
}
