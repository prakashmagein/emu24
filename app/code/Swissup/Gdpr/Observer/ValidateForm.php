<?php

namespace Swissup\Gdpr\Observer;

use Magento\Framework\App\Action\Action;

class ValidateForm implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection
     */
    private $forms;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->helper = $helper;
        $this->forms = $forms;
        $this->jsonHelper = $jsonHelper;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getControllerAction();
        $request = $this->request;
        $response = $this->response;

        if (!$request->isPost() || !$this->helper->isGdprEnabled()) {
            return;
        }

        // get form to validate
        $form = $this->forms->getItemByColumnValue(
            'action',
            $request->getFullActionName()
        );

        if (!$form) {
            return;
        }

        $receivedConsents = $request->getParam('swissup_gdpr_consent', []);
        foreach ($form->getConsents() as $consent) {
            if ($form->getSyncWith()) {
                $value = $request->getParam($form->getSyncWith(), 0);
                $receivedConsents[$consent['html_id']] = $value;
                $request->setParam('swissup_gdpr_consent', $receivedConsents);
            }

            if (!empty($receivedConsents[$consent['html_id']])) {
                continue;
            }

            if ($form->getIsRevokable()) {
                continue;
            }

            // allow to store post data into data_persistor or session if needed
            $this->eventManager->dispatch(
                'swissup_gdpr_form_validate_fail',
                [
                    'form' => $form,
                    'controller_action' => $controller,
                ]
            );

            $this->messageManager->addError(__('Please accept all consents.'));
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $redirectUrl = $this->redirect->getRedirectUrl();

            if ($request->isAjax()) {
                return $response->representJson(
                    $this->jsonHelper->jsonEncode([
                        'backUrl' => $redirectUrl
                    ])
                );
            } else {
                return $this->redirect->redirect(
                    $response,
                    $redirectUrl
                );
            }
        }
    }
}
