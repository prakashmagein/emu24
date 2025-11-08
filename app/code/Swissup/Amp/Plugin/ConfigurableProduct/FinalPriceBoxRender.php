<?php
namespace Swissup\Amp\Plugin\ConfigurableProduct;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class FinalPriceBoxRender
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject
     * @param string $result
     * @return string
     */
    public function afterRenderAmount(
        \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject,
        $result
    ) {
        if ($this->helper->canUseAmp() && $this->helper->isProductPage()) {
            $product = $this->registry->registry('product');
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $attributes = $product->getTypeInstance()->getConfigurableAttributes($product);
                $selectedArr = [];
                $selectedPrice = 'product';
                foreach ($attributes as $attribute) {
                    $selectedId = 'selected' . $attribute->getAttributeId();
                    $selectedArr[] = "product." . $selectedId . " != ''";
                    $selectedPrice .= '[product.' . $selectedId . ']';
                }
                $selectedCondition = implode(' && ', $selectedArr);

                // hide "As low as" when options selected
                $result = str_replace(
                    '<span class="price-label">',
                    '<span class="price-label" data-amp-bind-hidden="' . $selectedCondition . '">',
                    $result
                );

                // show configuration price
                $defaultPrice = $this->helper->getFormattedPrice($product);
                $price = $selectedPrice . ' ? ' . $selectedPrice . " : '" . $defaultPrice . "'";
                $result = str_replace(
                    '<span class="price">',
                    '<span class="price" data-amp-bind-text="' . $price . '">',
                    $result
                );
            }
        }

        return $result;
    }
}
