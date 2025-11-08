<?php

namespace Swissup\Gdpr\Observer;

class SaveCookieConsentOnLogin implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Visitor
     */
    private $customerVisitor;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\ClientConsentRepository
     */
    private $clientConsentRepository;

    /**
     * @var \Swissup\Gdpr\Model\CookieManager
     */
    private $cookieManager;

    /**
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param \Swissup\Gdpr\Model\ClientConsentRepository $clientConsentRepository
     * @param \Swissup\Gdpr\Model\CookieManager $cookieManager
     */
    public function __construct(
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\ClientConsentRepository $clientConsentRepository,
        \Swissup\Gdpr\Model\CookieManager $cookieManager
    ) {
        $this->customerVisitor = $customerVisitor;
        $this->helper = $helper;
        $this->clientConsentRepository = $clientConsentRepository;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cookieConsent = $this->clientConsentRepository->getCookieConsent();
        $allowedGroups = $this->cookieManager->getAllowedGroupCodes(true);

        if (!$allowedGroups) {
            return;
        }

        $title = $this->helper->getCookieConsentTitle($allowedGroups);

        if (!$cookieConsent->getId()) {
            $cookieConsent->addData($this->clientConsentRepository->getDefaultCookieConsentData());
        } elseif ($title === $cookieConsent->getConsentTitle()) {
            return;
        }

        $cookieConsent
            ->setConsentTitle($this->helper->getCookieConsentTitle($allowedGroups))
            ->save();
    }
}
