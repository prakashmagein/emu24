<?php

namespace Swissup\Gdpr\Controller\Cookie;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\ValidatorException;

class Accept extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    private $customerVisitor;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Visitor $customerVisitor
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->customerVisitor = $customerVisitor;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = new \Magento\Framework\DataObject();

        try {
            $this->validateRequest();

            // @see SaveClientConsents observer
            $this->getRequest()
                ->setParam('swissup_gdpr_consent', ['cookie' => 1])
                ->setParam('visitor_id', 'Guest-' . $this->customerVisitor->getId());

            $response->setSuccess(1);
        } catch (\Exception $e) {
            $response->setMessage($e->getMessage());
            $response->setError(1);
        }

        return $resultJson->setData($response);
    }

    /**
     * @return void
     * @throws ValidatorException
     */
    private function validateRequest()
    {
        if (!$this->getRequest()->isPost()) {
            throw new ValidatorException(__('Request must be POST.'));
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new ValidatorException(__('Invalid Form Key. Please refresh the page.'));
        }
    }
}
