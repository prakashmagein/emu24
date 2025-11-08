<?php

namespace Swissup\Gdpr\Model;

use Swissup\Gdpr\Model\ClientRequest;

class ClientConsentRepository
{
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var \Swissup\Gdpr\Model\ClientConsentFactory
     */
    private $customerSession;

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     */
    private $customerVisitor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\Config\Share $shareConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory $collectionFactory
    ) {
        $this->shareConfig = $shareConfig;
        $this->customerSession = $customerSession;
        $this->customerVisitor = $customerVisitor;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getDefaultCookieConsentData()
    {
        return array_merge($this->getDefaultConsentData(), [
            'form_id' => 'swissup:cookie-consent',
        ]);
    }

    /**
     * @return array
     */
    public function getDefaultConsentData()
    {
        return [
            'client_identity_field' => 'email',
            'client_identity' => $this->customerSession->getCustomer() ?
                $this->customerSession->getCustomer()->getEmail() :
                '',
            'customer_id' => $this->customerSession->getCustomerId(),
            'visitor_id' => $this->customerVisitor->getId(),
        ];
    }

    /**
     * @return \Swissup\Gdpr\Model\ClientConsent
     */
    public function getCookieConsent()
    {
        $consent = $this
            ->getAcceptedConsents('swissup:cookie-consent')
            ->getFirstItem();

        if (!$consent->getId()) {
            $consent->setConsentId('cookie');
        }

        return $consent;
    }

    /**
     * Get the list of previously accepted consents
     *
     * @param string|array $formId
     * @param string|array $identity
     * @param string $identityField
     * @return \Swissup\Gdpr\Model\ResourceModel\ClientConsent\Collection
     */
    public function getAcceptedConsents(
        $formId,
        $identity = null,
        $identityField = 'email'
    ) {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('form_id', ['in' => $formId]);

        $filters = array_filter([
            'website_id' => $this->storeManager->getWebsite()->getWebsiteId(),
            'customer_id' => $this->customerSession->getCustomerId(),
            'visitor_id' => $this->customerVisitor->getId(),
        ]);

        if (!empty($filters['customer_id'])) {
            unset($filters['visitor_id']);
        }

        if (is_array($identity)) {
            $filters = $identity;
        } elseif ($identity) {
            $filters['client_identity_field'] = $identityField;
            $filters['client_identity'] = $identity;
        }

        if (!$this->shareConfig->isWebsiteScope()) {
            unset($filters['website_id']);
        }

        foreach ($filters as $key => $value) {
            $collection->addFieldToFilter($key, $value);
        }

        return $collection;
    }
}
