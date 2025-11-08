<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

use Magento\Framework\Filter\FilterInput;

class PostCustomerProcessor extends \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor
{
    private $depracatedFilterInput = '\Zend_Filter_Input';

    public function filter($data)
    {
        $filterRules = [];

        if (class_exists(FilterInput::class)) {
            // FilterInput added only in magento 2.4.6
            $filterInput = new FilterInput($filterRules, [], $data);
        } elseif (class_exists($this->depracatedFilterInput)) {
            // zend filter removed since magento 2.4.6
            $filterInput = new $this->depracatedFilterInput($filterRules, [], $data);
        } else {
            throw new \RuntimeException(__('FilterInput class not found'));
        }

        return $filterInput->getUnescaped();
    }

    public function validateRequireEntry(array $data)
    {
        $requiredCustomerFields = ['weight' => __('Customer Weight')];
        $errors = true;
        foreach ($data as $customerField => $item) {
            if (in_array($customerField, array_keys($requiredCustomerFields)) && $item == '') {
                $errors = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredCustomerFields[$customerField])
                );
            }
        }
        return $errors;
    }
}
