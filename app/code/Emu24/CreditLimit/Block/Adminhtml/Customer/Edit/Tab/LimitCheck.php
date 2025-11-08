<?php
namespace Emu24\CreditLimit\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class LimitCheck extends Template implements TabInterface
{
    /**
     * Set template for the tab content
     *
     * @var string
     */
    protected $_template = 'Emu24_CreditLimit::customer/tab/limit_check.phtml';

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Limit Check');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Limit Check');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    private function getCustomer()
    {
        return $this->registry->registry('current_customer');
    }

    public function getRegNo(): string
    {
        $customer = $this->getCustomer();
        $attribute = $customer ? $customer->getCustomAttribute('regno') : null;
        return $attribute ? (string)$attribute->getValue() : '';
    }

    public function getCreditLimit(): string
    {
        $customer = $this->getCustomer();
        $attribute = $customer ? $customer->getCustomAttribute('credit_limit') : null;
        return $attribute ? (string)$attribute->getValue() : '';
    }
}
