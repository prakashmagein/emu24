<?php

namespace Swissup\Askit\Model\Notification;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;

class NotificationAbstract implements ObserverInterface
{
    const ASKIT_EMAIL_ENABLE = 'askit/email/enable';
    const ASKIT_EMAIL_IDENTITY = 'askit/email/identity';
    const ASKIT_CUSTOMER_NOTIFICATION_EMAIL_TEMPLATE = 'askit/email/customer_notification';
    const ASKIT_ADMIN_NOTIFICATION_EMAIL_TEMPLATE = 'askit/email/admin_notification';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var \Swissup\Askit\Helper\Url
     */
    protected $urlHelper;

     /**
      * @var \Magento\Store\Model\App\Emulation
      */
    protected $appEmulation;

    /**
     * @var EmailAddressValidator
     */
    protected $emailAddressValidator;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Swissup\Askit\Helper\Url $urlHelper
     * @param UrlInterface $urlBuilder
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param EmailAddressValidator $emailAddressValidato
     */
    public function __construct(
        /* ... */
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Swissup\Askit\Helper\Url $urlHelper,
        UrlInterface $urlBuilder,
        \Magento\Store\Model\App\Emulation $appEmulation,
        EmailAddressValidator $emailAddressValidator
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        /* ... */
        $this->urlHelper = $urlHelper;
        $this->urlHelper->setUrlBuilder($urlBuilder);

        $this->appEmulation = $appEmulation;
        $this->emailAddressValidator = $emailAddressValidator;
    }

    protected function _sendEmail($from, $to, $templateId, $vars, $store, $area = \Magento\Framework\App\Area::AREA_FRONTEND)
    {
        $enable = (bool) $this->scopeConfig->getValue(
            self::ASKIT_EMAIL_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$enable) {
            return;
        }
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => $area,
                'store' => $store->getId(),
            ])
            ->setTemplateVars($vars)
            ->setFrom($from)
            ->addTo($to['email'], $to['name'])
            ->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return $this;
    }
}
