<?php

namespace Swissup\Easybanner\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Swissup\Easybanner\Model\Rule\Condition\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Swissup\Easybanner\Model\Rule\Condition\BannerFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Swissup\Easybanner\Model\Rule\Condition\BannerFactory $conditionFactory,
        array $data = []
    ) {
        $this->_bannerFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType('Swissup\Easybanner\Model\Rule\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Product Attributes Combination (Product Page)'),
                    'value' => 'Swissup\Easybanner\Model\Rule\Condition\Product\Combine',
                ],
                [
                    'label' => __('Product Attributes Combination (Shopping Cart)'),
                    'value' => 'Swissup\Easybanner\Model\Rule\Condition\CartItem\Combine',
                ],
                [
                    'label' => __('Conditions Combination'),
                    'value' => 'Swissup\Easybanner\Model\Rule\Condition\Combine',
                ],
            ],
            $this->_bannerFactory->create()->getCombinedConditions()
        );
        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($bannerCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Banner|Combine $condition */
            $condition->collectValidatedAttributes($bannerCollection);
        }
        return $this;
    }
}
