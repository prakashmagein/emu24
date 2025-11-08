<?php

namespace Swissup\Gdpr\Plugin;

use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\Gdpr\Model\ClientConsentRepository;
use Swissup\Gdpr\Model\Config\Source\ClientConsentConfirmationStatuses;

class UpdateNewsletterConsent
{
    private ClientConsentRepository $clientConsentRepository;

    private StoreManagerInterface $storeManager;

    public function __construct(
        ClientConsentRepository $clientConsentRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->clientConsentRepository = $clientConsentRepository;
        $this->storeManager = $storeManager;
    }

    public function afterSave(Subscriber $subscriber, $result)
    {
        if (!$subscriber->isStatusChanged()) {
            return;
        }

        $subscribed = $subscriber->getSubscriberStatus() == Subscriber::STATUS_SUBSCRIBED;
        $unsubscribed = $subscriber->getSubscriberStatus() == Subscriber::STATUS_UNSUBSCRIBED;

        foreach ($this->getConsents($subscriber) as $consent) {
            $confirmed = $consent->getConformationStatus() == ClientConsentConfirmationStatuses::CONFIRMED;

            if ($subscribed && !$confirmed) {
                $consent->setConfirmationStatus(ClientConsentConfirmationStatuses::CONFIRMED)->save();
            } else if ($unsubscribed) {
                $consent->delete();
            }
        }

        return $result;
    }

    public function afterDelete(Subscriber $subscriber, $result)
    {
        foreach ($this->getConsents($subscriber) as $consent) {
            $consent->delete();
        }

        return $result;
    }

    private function getConsents($subscriber)
    {
        return $this->clientConsentRepository->getAcceptedConsents(
            [
                'magento:newsletter-subscription',
                'magento:newsletter-subscription-management',
            ],
            [
                'client_identity' => $subscriber->getSubscriberEmail(),
                'client_identity_field' => 'email',
                'website_id' => $this->storeManager->getStore($subscriber->getStoreId())->getWebsiteId(),
            ]
        );
    }
}
