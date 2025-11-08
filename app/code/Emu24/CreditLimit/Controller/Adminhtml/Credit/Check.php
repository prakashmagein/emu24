<?php
namespace Emu24\CreditLimit\Controller\Adminhtml\Credit;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Emu24\CreditLimit\Model\CreditSafe;

class Check extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    private $jsonFactory;
    private $customerRepository;
    private $creditSafe;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CustomerRepositoryInterface $customerRepository,
        CreditSafe $creditSafe
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customerRepository = $customerRepository;
        $this->creditSafe = $creditSafe;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $customerId = (int)$this->getRequest()->getParam('id');
        if (!$customerId) {
            return $result->setData(['success' => false, 'message' => __('Customer ID missing')]);
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

            $limit = $this->creditSafe->fetchCreditLimit($regNo);
            if ($limit === null) {
                throw new \Exception(__('Credit limit not found'));
            }

            $customer->setCustomAttribute('credit_limit', $limit);
            $this->customerRepository->save($customer);

            return $result->setData([
                'success' => true,
                'credit_limit' => $limit,
                'message' => __('Credit limit saved')
            ]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
