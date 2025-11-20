<?php
namespace Emu24\CreditLimit\Controller\Adminhtml\Credit;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Emu24\CreditLimit\Model\CreditSafe;
use Emu24\CreditLimit\Model\CreditReportFactory;
use Emu24\CreditLimit\Model\CreditReportRepository;

class Check extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    private $jsonFactory;
    private $customerRepository;
    private $creditSafe;
    private $creditReportFactory;
    private $creditReportRepository;
    private $jsonSerializer;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CustomerRepositoryInterface $customerRepository,
        CreditSafe $creditSafe,
        CreditReportFactory $creditReportFactory,
        CreditReportRepository $creditReportRepository,
        JsonSerializer $jsonSerializer
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customerRepository = $customerRepository;
        $this->creditSafe = $creditSafe;
        $this->creditReportFactory = $creditReportFactory;
        $this->creditReportRepository = $creditReportRepository;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute()
    {
        // Ensure no previous output corrupts the JSON payload
        while (ob_get_level()) {
            ob_end_clean();
        }

        $result = $this->jsonFactory->create();
        $customerId = (int)$this->getRequest()->getParam('id');
        if (!$customerId) {
            $customerId = (int)$this->getRequest()->getParam('customer_id');
        }
        if (!$customerId) {
            return $result->setJsonData($this->jsonSerializer->serialize([
                'success' => false,
                'message' => __('Customer ID missing'),
            ]));
        }
        try {
            $customer = $this->customerRepository->getById($customerId);
            $regNo = (string)$this->getRequest()->getParam('regno');
            if ($regNo === '') {
                $regNoAttr = $customer->getCustomAttribute('regno');
                $regNo = $regNoAttr ? $regNoAttr->getValue() : '';
            } else {
                $customer->setCustomAttribute('regno', $regNo);
            }

            if ($regNo === '') {
                throw new \Exception(__('Registration number is empty'));
            }

            $report = $this->creditSafe->fetchReport($regNo);
            $creditData = $report['credit']['creditLimit'] ?? [];
            $limit = $creditData['amount'] ?? null;
            if ($limit === null) {
                throw new \Exception(__('Credit limit not found'));
            }

            $customer->setCustomAttribute('credit_limit', $limit);
            $this->customerRepository->save($customer);

            $payload = $this->jsonSerializer->serialize($report);

            $reportModel = $this->creditReportFactory->create();
            $reportModel->setData([
                'customer_id'              => $customer->getId(),
                'regno'                    => $regNo,
                'company_id'               => $report['company']['companyId'] ?? null,
                'company_name'             => $report['company']['businessName'] ?? null,
                'credit_limit_amount'      => $limit,
                'credit_limit_currency'    => $creditData['currency'] ?? null,
                'credit_score_value'       => $report['credit']['creditScore']['value'] ?? null,
                'credit_score_description' => $report['credit']['creditScore']['description'] ?? null,
                'payload'                  => $payload,
            ]);
            $this->creditReportRepository->save($reportModel);

            $response = [
                'success' => true,
                'credit_limit' => $limit,
                'message' => __('Credit report saved'),
                'report'  => $report,
            ];

            return $result->setJsonData($this->jsonSerializer->serialize($response));
        } catch (\Exception $e) {
            return $result->setJsonData($this->jsonSerializer->serialize([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        }
    }
}
