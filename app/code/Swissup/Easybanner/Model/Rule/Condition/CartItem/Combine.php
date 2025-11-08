<?php

namespace Swissup\Easybanner\Model\Rule\Condition\CartItem;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Product\Subselect
{
    private $layout;
    private $checkoutSession;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(self::class);
        $this->checkoutSession = $checkoutSession;
        $this->layout = $layout;
    }

    /**
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($this->layout->isCacheable()) {
            return false;
        }

        try {
            return parent::validate(
                $this->checkoutSession->getQuote()->getShippingAddress()
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '>' => __('greater than'),
            '<' => __('less than'),
            '>=' => __('equals or greater than'),
            '<=' => __('equals or less than'),
        ]);

        return $this;
    }
}
