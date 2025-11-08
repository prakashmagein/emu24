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
 * @package    Ecomteck_CustomerCustomAttributes
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
declare(strict_types=1);

namespace Ecomteck\CustomerCustomAttributes\Model;

use Ecomteck\CustomerCustomAttributes\Api\Data\AttributeInterfaceFactory;
use Ecomteck\CustomerCustomAttributes\Api\CustomerAttributeRepositoryInterface;
use Magento\Customer\Model\AttributeFactory;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Customer\Model\Metadata\CustomerMetadata;
use Ecomteck\CustomerCustomAttributes\Helper\Customer as HelperCustomer;
use Ecomteck\CustomerCustomAttributes\Helper\Data as HelperData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\FormData;

class CustomerAttributeRepository implements CustomerAttributeRepositoryInterface
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;
    /**
     * @var AttributeInterfaceFactory
     */
    private $dataAttributeInterfaceFactory;
    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;
    /**
     * @var AttributeFactory
     */
    private $attributeFactory;
    /**
     * @var Attribute
     */
    private $resource;
    /**
     * @var CustomerMetadata
     */
    private $metadata;
    /**
     * @var HelperCustomer
     */
    private $helperCustomer;
    /**
     * @var HelperData
     */
    private $helperData;
    /**
     * @var FormData|mixed
     */
    private $formDataSerializer;
    /**
     * @var SetFactory
     */
    private $_attrSetFactory;
    /**
     * @var Config
     */
    private $_eavConfig;
    /**
     * @var int|\Magento\Eav\Model\Entity\Type|string
     */
    private $_entityType;

    /**
     * CustomerAttributeRepository constructor.
     * @param Attribute $resource
     * @param AttributeFactory $attributeFactory
     * @param AttributeInterfaceFactory $dataAttributeInterfaceFactory
     * @param CollectionFactory $attributeCollectionFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CustomerMetadata $metadata
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param SetFactory $attrSetFactory
     * @param Config $eavConfig
     * @param FormData|null $formDataSerializer
     */
    public function __construct(
        Attribute $resource,
        AttributeFactory $attributeFactory,
        AttributeInterfaceFactory $dataAttributeInterfaceFactory,
        CollectionFactory $attributeCollectionFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CustomerMetadata $metadata,
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        SetFactory $attrSetFactory,
        Config $eavConfig,
        FormData $formDataSerializer = null
    ) {
        $this->resource = $resource;
        $this->attributeFactory = $attributeFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->dataAttributeInterfaceFactory = $dataAttributeInterfaceFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->metadata = $metadata;
        $this->helperData = $helperData;
        $this->helperCustomer = $helperCustomer;
        $this->_attrSetFactory = $attrSetFactory;
        $this->_eavConfig = $eavConfig;
        $this->formDataSerializer = $formDataSerializer ?? ObjectManager::getInstance()->get(FormData::class);
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException|LocalizedException
     */
    public function save(
        \Ecomteck\CustomerCustomAttributes\Api\Data\AttributeInterface $attribute
    ) {

        $data = $this->extensibleDataObjectConverter->toNestedArray(
            $attribute,
            [],
            \Ecomteck\CustomerCustomAttributes\Api\Data\AttributeInterface::class
        );
        $attributeObject = $this->attributeFactory->create();

        if (isset($data['attribute_id']) && $data['attribute_id']) {
            $attributeObject->load($data['attribute_id']);
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                throw new CouldNotSaveException(__('You cannot edit this attribute.'));
            }

            $data['attribute_code'] = $attributeObject->getAttributeCode();
            $data['is_user_defined'] = $attributeObject->getIsUserDefined();
            $data['frontend_input'] = $attributeObject->getFrontendInput();
            $data['is_user_defined'] = $attributeObject->getIsUserDefined();
            $data['is_system'] = $attributeObject->getIsSystem();
            if (isset($data['used_in_forms']) && is_array($data['used_in_forms'])) {
                $data['used_in_forms'][] = 'adminhtml_customer';
            }
        } else {
            $data['backend_model'] = $this->helperData->getAttributeBackendModelByInputType(
                $data['frontend_input']
            );
            $data['source_model'] = $this->helperData->getAttributeSourceModelByInputType($data['frontend_input']);
            $data['backend_type'] = $this->helperData->getAttributeBackendTypeByInputType($data['frontend_input']);
            $data['is_user_defined'] = 1;
            $data['is_system'] = 0;

            // add set and group info
            $data['attribute_set_id'] = $this->_getEntityType()->getDefaultAttributeSetId();
            $attrSet = $this->_attrSetFactory->create();
            $data['attribute_group_id'] = $attrSet->getDefaultGroupId($data['attribute_set_id']);
            $data['used_in_forms'][] = 'adminhtml_customer';
        }

        $data['entity_type_id'] = $this->_getEntityType()->getId();
        $data['validate_rules'] = $this->helperData->getAttributeValidateRules($data['frontend_input'], $data);

        $validateRulesErrors = $this->helperData->checkValidateRules(
            $data['frontend_input'],
            $data['validate_rules']
        );
        if (count($validateRulesErrors)) {
            foreach ($validateRulesErrors as $message) {
                throw new CouldNotSaveException(__(
                    'Could not save the attribute: %1',
                    $message
                ));
            }
        }

        $attributeObject->addData($data);

        /**
         * Check "Use Default Value" checkboxes values
         */
        if (isset($data['use_default']) && $data['use_default']) {
            foreach ($data['use_default'] as $key) {
                $attributeObject->setData($key, null);
            }
        }

        try {
            $attributeObject->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the attribute: %1',
                $exception->getMessage()
            ));
        }
        return $attributeObject->getData();
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotDeleteException
     */
    public function deleteById($attributeId)
    {
        try {
            $attributeModel = $this->attributeFactory->create();
            $this->resource->load($attributeModel, $attributeId);
            if (!$attributeModel->getData()) {
                throw new CouldNotDeleteException(__(
                    'Customer Attribute %1 does not exits',
                    $attributeId
                ));
            }
            $this->resource->delete($attributeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Customer Attribute: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Return Customer Address Entity Type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    protected function _getEntityType()
    {
        if ($this->_entityType === null) {
            $this->_entityType = $this->_eavConfig->getEntityType('customer');
        }
        return $this->_entityType;
    }
}
