<?php
namespace Swissup\SoldTogether\Model\Config\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    const AMAZON_DEFAULT = 'amazon-default';
    const AMAZON_STRIPE = 'amazon-stripe';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = [
            self::AMAZON_DEFAULT => __('Default (Amazon inspired)'),
            self::AMAZON_STRIPE => __('Stripe')
        ];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
