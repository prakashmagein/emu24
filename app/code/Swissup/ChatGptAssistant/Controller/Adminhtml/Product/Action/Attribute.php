<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Product\Action;

use Magento\Backend\App\Action;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;

abstract class Attribute extends Action
{
    const ADMIN_RESOURCE = 'Swissup_ChatGptAssistant::bulk_product_generate';

    protected AttributeHelper $attributeHelper;

    public function __construct(
        Action\Context $context,
        AttributeHelper $attributeHelper
    ) {
        parent::__construct($context);
        $this->attributeHelper = $attributeHelper;
    }

    /**
     * Validate selection of products for mass update
     *
     * @return boolean
     */
    protected function _validateProducts()
    {
        $error = false;
        $productIds = $this->attributeHelper->getProductIds();
        if (!is_array($productIds)) {
            $error = __('Please select products for attributes update.');
        } elseif (!$this->_objectManager->create(\Magento\Catalog\Model\Product::class)
            ->isProductsHasSku($productIds)) {
            $error = __('Please make sure to define SKU values for all processed products.');
        }

        if ($error) {
            $this->messageManager->addErrorMessage($error);
        }

        return !$error;
    }
}
