<?php

namespace Swissup\Gdpr\Block\Privacy;

use Magento\Framework\View\Element\Template;

class AcceptedConsents extends Template
{
    private $collectionFactory;
    private $forms;
    private $customerSession;
    /**
     * @var string
     */
    protected $_template = 'Swissup_Gdpr::privacy/accepted-consents.phtml';

    /**
     * @param Template\Context                                                  $context
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory $collectionFactory
     * @param \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection     $forms
     * @param \Magento\Customer\Model\Session                                   $customerSession
     * @param array                                                             $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory $collectionFactory,
        \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->forms = $forms;
        $this->customerSession = $customerSession;
    }

    /**
     * Get the list of consents grouped by form_id
     *
     * @return array
     */
    public function getGroupedConsents()
    {
        $result = [];
        $consents = $this->getConsents();
        $formIds = $consents->getColumnValues('form_id');
        $formIds = array_unique($formIds);

        foreach ($formIds as $formId) {
            $form = $this->forms->getItemById($formId);
            if (!$form) {
                continue;
            }

            $formConsents = $consents->getItemsByColumnValue('form_id', $formId);

            // Multiple consents on the same form.
            // Client subscribed with multiple email addresses.
            // Let's add identity_key to each consent if they different.
            $titles = array_map(fn ($consent) => $consent->getConsentTitle(), $formConsents);
            $uniqueTitles = array_unique($titles);

            if (count($titles) !== count($uniqueTitles)) {
                foreach ($formConsents as $consent) {
                    $consent->setConsentTitle(
                        "{$consent->getConsentTitle()} [{$consent->getClientIdentity()}]"
                    );
                }
            }

            $result[$form->getShortname()] = $formConsents;
        }

        return $result;
    }

    /**
     * Get the list of accepted consents
     *
     * @return \Swissup\Gdpr\Model\ResourceModel\ClientConsent\Collection
     */
    public function getConsents()
    {
        $collection = $this->collectionFactory->create();

        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        } else {
            $collection->addFieldToFilter('client_identity', $this->getVerifiedIdentity());
        }

        return $collection;
    }

    /**
     * @todo: implement token verification for the guest customers
     *
     * @return string|boolean
     */
    private function getVerifiedIdentity()
    {
        return false;

        // $token = $this->getRequest()->getParam('token');
        // if ($token) {
        //     return $this->getRequest()->getParam('identity');
        // }
        // return false;
    }
}
