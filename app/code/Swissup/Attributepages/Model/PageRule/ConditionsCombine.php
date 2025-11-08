<?php

namespace Swissup\Attributepages\Model\PageRule;

class ConditionsCombine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        array $data = []
    ) {
        $this->productResource = $productResource;

        parent::__construct($context, $conditionFactory, $data);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->getProductAttributes();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            // filter non-layered navigtion attrs
            $attributes[] = [
                'value' => 'Swissup\Attributepages\Model\PageRule\ProductCondition|' . $code,
                'label' => $label,
            ];
        }

        return [
            ['value' => '', 'label' => __('Please choose a condition to add.')],
            ['label' => __('Product Attribute'), 'value' => $attributes],
        ];
    }

    private function getProductAttributes()
    {
        $productAttributes = $this->productResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        $attributes['category_ids'] = __('Category');

        $supportedFilters = [
            'select',
            'multiselect',
            'boolean',
        ];

        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute->getIsFilterable()) {
                continue;
            }

            if (!in_array($attribute->getFrontendInput(), $supportedFilters)) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        asort($attributes);

        return $attributes;
    }
}
