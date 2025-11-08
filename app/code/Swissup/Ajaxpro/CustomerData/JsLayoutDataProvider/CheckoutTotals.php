<?php

namespace Swissup\Ajaxpro\CustomerData\JsLayoutDataProvider;

use Magento\Customer\CustomerData\JsLayoutDataProviderInterface;

/**
 * Checkout totals js layout data provider
 */
class CheckoutTotals implements JsLayoutDataProviderInterface
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->taxConfig = $taxConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [
            'components' => [
                'ajaxpro_minicart_content' => [
                    'children' => [
                        'subtotal.container' => [
                            'children' => [
                                'subtotal' => [
                                    'children' => [
                                        'subtotal.totals' => [
                                            'config' => $this->getTotalsConfig(),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
//                'minicart_content' => [
//                    'children' => [
//                        'subtotal.container' => [
//                            'children' => [
//                                'subtotal' => [
//                                    'children' => [
//                                        'subtotal.totals' => [
//                                            'config' => $this->getTotalsConfig(),
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
            ],
        ];
    }

    /**
     * Get totals config
     *
     * @return array
     */
    protected function getTotalsConfig()
    {
        return [
            'display_cart_subtotal_incl_tax' => (int) $this->taxConfig->displayCartSubtotalInclTax(),
            'display_cart_subtotal_excl_tax' => (int) $this->taxConfig->displayCartSubtotalExclTax(),
        ];
    }
}
