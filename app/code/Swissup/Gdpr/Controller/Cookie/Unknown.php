<?php

namespace Swissup\Gdpr\Controller\Cookie;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\ValidatorException;

class Unknown extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Swissup\Gdpr\Model\BlockedCookieRepository
     */
    private $blockedCookieRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Swissup\Gdpr\Model\BlockedCookieRepository $blockedCookieRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Swissup\Gdpr\Model\BlockedCookieRepository $blockedCookieRepository
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->blockedCookieRepository = $blockedCookieRepository;
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

        $names = $this->getRequest()->getParam('name');
        if (!is_array($names)) {
            $names = [$names];
        }

        try {
            $this->validateRequest();

            foreach ($names as $name) {
                $this->blockedCookieRepository->registerCookie([
                    'name' => $name,
                    'description' =>
                        'Some JS script made an attempt to create this cookie at '
                        . $this->getRequest()->getParam('location')
                ]);
            }

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
