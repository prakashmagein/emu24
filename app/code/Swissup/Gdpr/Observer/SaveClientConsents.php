<?php

namespace Swissup\Gdpr\Observer;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

class SaveClientConsents implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\ClientConsentFactory
     */
    private $clientConsentFactory;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection
     */
    private $forms;

    /**
     * @var \Swissup\Gdpr\Model\ClientConsentRepository
     */
    private $clientConsentRepository;

    /**
     * @var \Swissup\Gdpr\Model\ClientConsentFactory
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    private EventManager $eventManager;

    private StoreManager $storeManager;

    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param \Swissup\Gdpr\Model\ClientConsentFactory $clientConsentFactory
     * @param \Swissup\Gdpr\Model\ClientConsentRepository $clientConsentRepository
     * @param \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\ClientConsentFactory $clientConsentFactory,
        \Swissup\Gdpr\Model\ClientConsentRepository $clientConsentRepository,
        \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request,
        StoreManager $storeManager,
        EventManager $eventManager
    ) {
        $this->helper = $helper;
        $this->forms = $forms;
        $this->clientConsentRepository = $clientConsentRepository;
        $this->clientConsentFactory = $clientConsentFactory;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $this->request;

        if (!$request->isPost() || !$this->helper->isGdprEnabled()) {
            return;
        }

        // All data is already validated in predispatch observer,
        // so, if no consents are received - do nothing.
        $receivedConsents = $request->getParam('swissup_gdpr_consent', []);
        if (!$receivedConsents) {
            return;
        }

        // get form with consents
        $form = $this->forms->getItemByColumnValue(
            'action',
            $request->getFullActionName()
        );

        if (!$form) {
            return;
        }

        // Prepare client_consent data
        if ($this->customerSession->getCustomerId() && !$form->getForceClientIdentityField()) {
            $identityField = 'email';
            $identity = $this->customerSession->getCustomer()->getEmail();
        } else {
            $identityField = $form->getClientIdentityField() ?: 'email';
            $identity = $request->getParam($identityField, '');
        }

        $data = array_merge($this->clientConsentRepository->getDefaultConsentData(), [
            'client_identity_field' => $identityField,
            'client_identity' => $identity,
            'form_id' => $form->getId(),
        ]);

        if (empty($data['client_identity'])) {
            return;
        }

        // save and update ClientConsents
        $acceptedConsents = $this->clientConsentRepository->getAcceptedConsents(
            $form->getId(),
            $identity,
            $identityField
        );
        foreach ($form->getConsents() as $consent) {
            if (!isset($receivedConsents[$consent['html_id']])) {
                continue;
            }

            $value = $receivedConsents[$consent['html_id']];
            $model = $acceptedConsents->getItemByColumnValue(
                'consent_id',
                $consent['html_id']
            );

            if (empty($value)) {
                if ($form->getIsRevokable() && $model) {
                    $model->delete();
                }
                continue;
            }

            if ($model) {
                $model->addData($data)->unsUpdatedAt();
            } else {
                $model = $this->clientConsentFactory->create();
                $model->setData($data)
                    ->setConsentId($consent['html_id']);
            }

            $title = $consent['title'];
            if ($form->getId() === 'swissup:cookie-consent') {
                $title = $this->helper->getCookieConsentTitle(
                    $request->getParam('groups', [])
                );
            }

            $model->setConsentTitle($title)
                ->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());

            $this->eventManager->dispatch(
                'swissup_gdpr_consent_save_before',
                ['consent' => $model]
            );

            $model->save();
        }
    }
}
