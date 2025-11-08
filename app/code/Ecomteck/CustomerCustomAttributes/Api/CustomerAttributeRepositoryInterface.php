<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_TableRateShipping
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
declare(strict_types=1);

namespace Ecomteck\CustomerCustomAttributes\Api;

interface CustomerAttributeRepositoryInterface
{

    /**
     * Save ShippingFilter
     * @param \Ecomteck\CustomerCustomAttributes\Api\Data\AttributeInterface $customerAttribute
     * @return \Ecomteck\CustomerCustomAttributes\Api\Data\AttributeInterface
     */
    public function save(
        \Ecomteck\CustomerCustomAttributes\Api\Data\AttributeInterface $customerAttribute
    );

    /**
     * Delete Attribute by Code
     * @param string $attributeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($attributeId);
}
