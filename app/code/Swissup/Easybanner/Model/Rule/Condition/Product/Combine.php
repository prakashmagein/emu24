<?php

namespace Swissup\Easybanner\Model\Rule\Condition\Product;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Swissup\Easybanner\Helper\Condition
     */
    private $helper;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Swissup\Easybanner\Helper\Condition $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Swissup\Easybanner\Helper\Condition $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->setType(\Swissup\Easybanner\Model\Rule\Condition\Product\Combine::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->helper->getProductAttributes();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Swissup\Easybanner\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );
        return $conditions;
    }

    /**
     * @return $this
     */
    public function collectValidatedAttributes($collection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($collection);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml()
            . __(
                "If customer is viewing the product with %1 of these conditions %2:",
                $this->getAggregatorElement()->getHtml(),
                $this->getValueElement()->getHtml()
            );

        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Validate a condition with the checking of the children values
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function _isValid($entity)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->helper->getCurrentProduct();

        if (!$product) {
            return false;
        }

        $valid = parent::_isValid($product);

        if (!$valid && $product->isComposite($product)) {
            // These methods are safe to use, as they use memory caching and
            // Magento calls them before us.
            $typeInstance = $product->getTypeInstance();
            switch ($product->getTypeId()) {
                case Configurable::TYPE_CODE:
                    $children = $typeInstance->getUsedProducts($product);
                    break;
                case Grouped::TYPE_CODE:
                    $children = $typeInstance->getAssociatedProducts($product);
                    break;
                case Bundle::TYPE_CODE:
                    $children = $typeInstance->getSelectionsCollection(
                        $typeInstance->getOptionsIds($product), $product
                    );
                    break;
                default:
                    // third-party product type?
                    return $valid;
            }

            $attributes = $this->helper->getProductAttributes();
            foreach ($children as $simpleProduct) {
                foreach ($attributes as $code => $label) {
                    if ($simpleProduct->hasData($code)) {
                        continue;
                    }
                    if ($product->hasData($code)) {
                        $simpleProduct->setData($code, $product->getData($code));
                    }
                }

                $valid = parent::_isValid($simpleProduct);

                if ($valid) {
                    break;
                }
            }
        }

        return $valid;
    }
}
