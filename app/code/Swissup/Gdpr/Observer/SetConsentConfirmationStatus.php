<?php

namespace Swissup\Gdpr\Observer;

use Magento\Newsletter\Model\Subscriber;
use Swissup\Gdpr\Helper\Data as GdprHelper;
use Swissup\Gdpr\Model\Config\Source\ClientConsentConfirmationStatuses;

class SetConsentConfirmationStatus implements \Magento\Framework\Event\ObserverInterface
{
    private $subscriber;

    private $helper;

    public function __construct(
        Subscriber $subscriber,
        GdprHelper $helper
    ) {
        $this->subscriber = $subscriber;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $consent = $observer->getConsent();

        if ($consent->hasConfirmationStatus()) {
            return;
        }

        match ($consent->getFormId()) {
            'magento:newsletter-subscription',
            'magento:newsletter-subscription-management' => $this->processNewsletterConsent($consent),
            default => '',
        };
    }

    private function processNewsletterConsent($consent)
    {
        if (!$this->helper->getConfigValue(Subscriber::XML_PATH_CONFIRMATION_FLAG)) {
            return;
        }

        $subscriber = $this->subscriber->loadBySubscriberEmail(
            $consent->getClientIdentity(),
            $consent->getWebsiteId()
        );

        $consent->setConfirmationStatus(
            $subscriber->isSubscribed()
                ? ClientConsentConfirmationStatuses::CONFIRMED
                : ClientConsentConfirmationStatuses::PENDING
        );
    }
}
