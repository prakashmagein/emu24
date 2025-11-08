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

namespace Ecomteck\CustomerCustomAttributes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Laminas\Validator\StringLength;

class CustomerAttributeBeforeSave implements ObserverInterface
{
    /**
     * Before save observer for customer attribute
     *
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();

        if ($attribute instanceof \Magento\Customer\Model\Attribute && $attribute->isObjectNew()) {
            // Maximum allowed length for attribute code minus suffix
            $attributeCodeMaxLength = \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH - 9;

            $validator = new StringLength(['max' => $attributeCodeMaxLength]);
            if (!$validator->isValid($attribute->getAttributeCode())) {
                throw new LocalizedException(
                    __('An attribute code must not be more than %1 characters.', $attributeCodeMaxLength)
                );
            }
        }

        return $this;
    }
}
