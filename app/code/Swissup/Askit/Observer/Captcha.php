<?php
namespace Swissup\Askit\Observer;

use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\ReCaptchaUi\Model\CaptchaResponseResolverInterface;
use Magento\ReCaptchaUi\Model\ErrorMessageConfigInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;
use Swissup\Askit\Model\CaptchaProvider;

class Captcha implements ObserverInterface
{
    protected ActionFlag $actionFlag;
    protected CaptchaProvider $captchaProvider;
    protected MessageManager $messageManager;
    protected RedirectInterface $redirect;

    private DataPersistorInterface $dataPersistor;
    private string $formId;

    public function __construct(
        CaptchaProvider $captchaProvider,
        ActionFlag $actionFlag,
        MessageManager $messageManager,
        RedirectInterface $redirect,
        $formId = 'swissup_askit_new_question_form'
    ) {
        $this->captchaProvider = $captchaProvider;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->formId = $formId;
    }

    /**
     * Check CAPTCHA on New Question
     */
    public function execute(Observer $observer)
    {
        $controller = $observer->getControllerAction();

        if ($this->processMagentoRecaptcha($controller)) {
            return;
        }

        $this->processMagentoCaptcha($controller);
    }

    private function processMagentoRecaptcha($controller)
    {
        $request = $controller->getRequest();
        $isAjax = $request->isAjax();

        if (!$this->captchaProvider->getRecaptchaConfigResolver($this->formId)) {
            return;
        }

        $objectManager = ObjectManager::getInstance();
        $isRecaptchaEnabled = $objectManager->get(IsCaptchaEnabledInterface::class);
        if (!$isRecaptchaEnabled->isCaptchaEnabledFor($this->formId)) {
            return;
        }

        try {
            $reCaptchaResponse = $objectManager
                ->get(CaptchaResponseResolverInterface::class)
                ->resolve($request);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            if (!$isAjax) {
                $this->sendRedirect($request, $controller);
            }
        }

        $validationConfigResolver = $objectManager->get(ValidationConfigResolverInterface::class);
        $validationConfig = $validationConfigResolver->get($this->formId);
        $captchaValidator = $objectManager->get(ValidatorInterface::class);
        $validationResult = $captchaValidator->isValid($reCaptchaResponse, $validationConfig);
        if ($validationResult->isValid() === false) {
            $errorMessageConfig = $objectManager->get(ErrorMessageConfigInterface::class);
            $this->messageManager->addErrorMessage($errorMessageConfig->getValidationFailureMessage());
        }

        if (!$isAjax) {
            $this->sendRedirect($request, $controller);
        }

        return true;
    }

    private function processMagentoCaptcha($controller)
    {
        $request = $controller->getRequest();

        /** @var \Magento\Captcha\Model\DefaultModel $captcha */
        $captcha = $this->captchaProvider->getCaptcha($this->formId);
        if (!$captcha || !$captcha->isRequired()) {
            return;
        }

        $objectManager = ObjectManager::getInstance();
        $captchaStringResolver = $objectManager->get(CaptchaStringResolver::class);
        if (!$captcha->isCorrect($captchaStringResolver->resolve($request, $this->formId))) {
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA.'));
            if (!$request->isAjax()) {
                $this->sendRedirect($request, $controller);
            }
        }

        return true;
    }

    private function sendRedirect($request, $controller)
    {
        $dataPersistor = $this->getDataPersistor();
        if ($dataPersistor) {
            $dataPersistor->set($this->formId, $request->getPostValue());
        }

        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        $this->redirect->redirect($controller->getResponse(), $this->redirect->getRefererUrl());
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface|boolean
     */
    protected function getDataPersistor()
    {
        $class = \Magento\Framework\App\Request\DataPersistor::class;
        if (!class_exists($class, false)) {
            return false;
        }
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }
}
