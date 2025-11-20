<?php
namespace Emu24\CreditLimit\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Emu24\CreditLimit\Model\CreditReportRepository;

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

    /**
     * @var CreditReportRepository
     */
    private $creditReportRepository;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        CreditReportRepository $creditReportRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->creditReportRepository = $creditReportRepository;
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
        $report = $this->getLatestReport();
        if (isset($report['credit']['creditLimit']['amount'])) {
            return (string)$report['credit']['creditLimit']['amount'];
        }

        $customer = $this->getCustomer();
        $attribute = $customer ? $customer->getCustomAttribute('credit_limit') : null;
        return $attribute ? (string)$attribute->getValue() : '';
    }

    public function getReportJson(): string
    {
        $report = $this->getLatestReport();
        return $report ? json_encode($report, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) : 'null';
    }

    public function getLastCheckedAt(): ?string
    {
        $creditReport = $this->getLatestReportModel();
        return $creditReport ? $creditReport->getData('created_at') : null;
    }

    public function getCheckUrl(): string
    {
        return $this->getUrl('creditlimit/credit/check');
    }

    private function getLatestReport(): ?array
    {
        $creditReport = $this->getLatestReportModel();
        if ($creditReport && $creditReport->getPayload()) {
            $data = json_decode($creditReport->getPayload(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }

        return null;
    }

    private function getLatestReportModel()
    {
        $customer = $this->getCustomer();
        if (!$customer || !$customer->getId()) {
            return null;
        }

        return $this->creditReportRepository->getLatestByCustomerId((int)$customer->getId());
    }
}
