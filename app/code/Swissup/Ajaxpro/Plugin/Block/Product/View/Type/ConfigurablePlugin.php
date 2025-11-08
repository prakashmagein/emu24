<?php
/**
 * Plugin for Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
 */
namespace Swissup\Ajaxpro\Plugin\Block\Product\View\Type;

class ConfigurablePlugin
{
    /**
     *
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    private $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;

    /**
     * @param \Swissup\Ajaxpro\Helper\Config $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Swissup\Ajaxpro\Helper\Config $helper,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->helper = $helper;
        $this->serializer = $serializer;
    }

    /**
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    private function isAjaxproProductViewRequest(\Magento\Framework\App\Request\Http $request)
    {
        if (!$request->isAjax()) {
            return false;
        }

        $sectionNames = $request->getParam('sections');
        $sectionNames = $sectionNames ? array_unique(\explode(',', $sectionNames)) : [];
        if (!in_array('ajaxpro-product', $sectionNames)) {
            return false;
        }

        $ajaxproParam = $request->getParam('ajaxpro');
        if (!isset($ajaxproParam['product_id'])) {
            return false;
        }

        return true;
    }

    /**
     * Add containerId
     *
     * @param  \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param  string                               $result
     * @return string
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        if (!$this->isAjaxproProductViewRequest($subject->getRequest())) {
            return $result;
        }

        json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $result;
        }
        $jsonConfig = $this->serializer->unserialize($result);
        $isEnabled = $this->helper->isProductViewEnabled();

        if (!isset($jsonConfig['containerId']) && $isEnabled) {
            $jsonConfig['containerId'] = '#ajaxpro-catalog\\.product\\.view';
            $result = $this->serializer->serialize($jsonConfig);
        }

        return $result;
    }

    /**
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param array $result
     * @return array
     */
    public function afterGetCacheKeyInfo(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        array $result
    ) {
        if (!$this->isAjaxproProductViewRequest($subject->getRequest())) {
            return $result;
        }

        $isEnabled = $this->helper->isProductViewEnabled();
        if ($isEnabled) {
            $result[] = 'ajaxpro-catalog.product.view';
        }

        return $result;
    }
}
