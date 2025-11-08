<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

use Magento\Framework\Filter\FilterInput;

class PostOrderProcessor extends \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor
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
        $requiredOrderFields = ['weight' => __('Order Weight')];
        $errors = true;
        foreach ($data as $orderField => $item) {
            if (in_array($orderField, array_keys($requiredOrderFields)) && $item == '') {
                $errors = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredOrderFields[$orderField])
                );
            }
        }
        return $errors;
    }
}
