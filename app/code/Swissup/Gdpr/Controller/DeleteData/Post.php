<?php

namespace Swissup\Gdpr\Controller\DeleteData;

use Swissup\Gdpr\Model\ClientRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var \Magento\Customer\Model\CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Swissup\Gdpr\Model\ClientRequestRepository
     */
    private $clientRequestRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\CustomerAuthUpdate $customerAuthUpdate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Swissup\Gdpr\Model\ClientRequestRepository $clientRequestRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\CustomerAuthUpdate $customerAuthUpdate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Session $customerSession,
        \Swissup\Gdpr\Model\ClientRequestRepository $clientRequestRepository
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRegistry = $customerRegistry;
        $this->customerAuthUpdate = $customerAuthUpdate;
        $this->dateTime = $dateTime;
        $this->customerSession = $customerSession;
        $this->clientRequestRepository = $clientRequestRepository;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }

        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Delete all personal data
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->getRequest()->isPost()) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addError(__('Invalid Form Key. Please refresh the page.'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $data = $this->getRequest()->getPostValue();
        if (empty($data['confirm'])) {
            $this->messageManager->addError(__('Please accept all required checkboxes'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        try {
            $customerId = $this->customerSession->getCustomerId();

            // 1. lock account for a 1 month
            $nextMonth = new \DateTime();
            $nextMonth->add(new \DateInterval('P1M'));
            $customer = $this->customerRegistry->retrieve($customerId);
            $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
            $customerSecure->setLockExpires($this->dateTime->formatDate($nextMonth));
            $this->customerAuthUpdate->saveAuth($customerId);

            // 2. add entry into client_request table
            $this->clientRequestRepository->add([
                'client_identity' => $customer->getEmail(),
                'customer_id' => $customer->getId(),
                'type' => ClientRequest::TYPE_DATA_DELETE,
                'status' => ClientRequest::STATUS_CONFIRMED,
            ]);

            // 3. logout from account
            $this->customerSession->logout();
            $this->customerSession->start();

            $this->customerSession->setGdprDataRemovedFlag(true);

            $resultRedirect->setPath('*/*/success');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
