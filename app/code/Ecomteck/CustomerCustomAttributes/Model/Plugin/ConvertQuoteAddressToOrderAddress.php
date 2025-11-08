<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Model\Plugin;

class ConvertQuoteAddressToOrderAddress
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Helper\Data
     */
    private $customerData;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Helper\Data $customerData
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Helper\Data $customerData
    ) {
        $this->customerData = $customerData;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\AddressInterface $quoteAddress
     * @param array $data
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress,
        $data = []
    ) {
        $orderAddress = $proceed($quoteAddress, $data);
        $attributes = $this->customerData->getCustomerAddressUserDefinedAttributeCodes();
        foreach ($attributes as $attribute) {
            $orderAddress->setData($attribute, $quoteAddress->getData($attribute));
        }
        return $orderAddress;
    }
}
