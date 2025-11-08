<?php
namespace Swissup\Reviewreminder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * Path to store config is reminder enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED            = 'reviewreminder/general/enabled';
    /**
     * Path to store config is reminder enabled for guests
     *
     * @var string
     */
    const XML_PATH_ENABLED_GUEST      = 'reviewreminder/general/enabled_for_guests';
    /**
     * Path to store config number of emails per cron
     *
     * @var string
     */
    const XML_PATH_EMAILS_NUM_PER_CRON = 'reviewreminder/general/emails_per_cron';
    /**
     * Path to store config reminder default status
     *
     * @var string
     */
    const XML_PATH_DEFAULT_STATUS = 'reviewreminder/general/default_status';
    /**
     * Path to store config process orders
     *
     * @var string
     */
    const XML_PATH_ALLOW_SPECIFIC = 'reviewreminder/email/allow_specific_order';
    /**
     * Path to store config consider orders statuses
     *
     * @var string
     */
    const XML_PATH_SPECIFIC_ORDER_STATUSES = 'reviewreminder/email/specific_order';
    /**
     * Path to store config email subject
     *
     * @var string
     */
    const XML_PATH_EMAIL_SUBJECT = 'reviewreminder/email/email_subject';
    /**
     * Path to store config email template
     *
     * @var string
     */
    const XML_PATH_EMAIL_TEMPLATE = 'reviewreminder/email/email_template';
    /**
     * Path to store config email send from contact
     *
     * @var string
     */
    const XML_PATH_EMAIL_SEND_FROM = 'reviewreminder/email/send_from';
    /**
     * Path to store config send email after days
     *
     * @var string
     */
    const XML_PATH_SEND_EMAIL_AFTER = 'reviewreminder/email/send_after';

    /**
     * Path to store config trustedshops url
     *
     * @var string
     */
    const XML_PATH_TS_URL = 'reviewreminder/trustedshops/url';

    /**
     * Path to store config trustedshops customer number
     *
     * @var string
     */
    const XML_PATH_TS_CUSTOMER_NUMBER = 'reviewreminder/trustedshops/customer_number';

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($context);
    }
    protected function _getConfig($key, $store = null)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $store);
    }
    public function isEnabled($storeId)
    {
        return (bool)$this->_getConfig(self::XML_PATH_ENABLED, $storeId);
    }
    public function isEnabledForGuests($storeId)
    {
        return (bool)$this->_getConfig(self::XML_PATH_ENABLED_GUEST, $storeId);
    }
    public function getNumOfEmailsPerCron()
    {
        $numToSend = abs((int)$this->_getConfig(self::XML_PATH_EMAILS_NUM_PER_CRON));
        return $numToSend ? $numToSend : 10;
    }
    public function getDefaultStatus($storeId)
    {
        return abs((int)$this->_getConfig(self::XML_PATH_DEFAULT_STATUS, $storeId));
    }
    public function allowSpecificStatuses()
    {
        return (bool)$this->_getConfig(self::XML_PATH_ALLOW_SPECIFIC);
    }
    public function specificOrderStatuses()
    {
        return explode(',', $this->_getConfig(self::XML_PATH_SPECIFIC_ORDER_STATUSES));
    }
    public function getEmailSubject($storeId)
    {
        return (String)$this->_getConfig(self::XML_PATH_EMAIL_SUBJECT, $storeId);
    }
    public function getEmailTemplate($storeId)
    {
        return (String)$this->_getConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
    }
    public function getEmailSendFrom($storeId)
    {
        return (String)$this->_getConfig(self::XML_PATH_EMAIL_SEND_FROM, $storeId);
    }
    public function getSendEmailAfter()
    {
        return abs((int)$this->_getConfig(self::XML_PATH_SEND_EMAIL_AFTER));
    }

    /**
     * Generate TrustedShops review link
     * @param  string $email
     * @param  string $orderId
     * @param  int $storeId
     * @return string
     */
    public function getTrustedShopsLink($email, $orderId, $storeId)
    {
        $url = $this->_getConfig(self::XML_PATH_TS_URL, $storeId);
        $customerNumber = $this->_getConfig(self::XML_PATH_TS_CUSTOMER_NUMBER, $storeId);

        if (empty($url) || empty($customerNumber)) {
            return '';
        }

        $email = base64_encode($email);
        $orderId = base64_encode($orderId);
        $url .= "_$customerNumber.html?buyerEmail=$email&shopOrderID=$orderId&channel=cmF0ZW5vd2J1dHRvbg";

        return $url;
    }
}
