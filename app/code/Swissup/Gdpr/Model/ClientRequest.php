<?php

namespace Swissup\Gdpr\Model;

class ClientRequest extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_DATA_EXPORT = 1;
    const TYPE_DATA_DELETE = 2;
    const TYPE_DATA_VIEW   = 3; // @todo: implement privacy-tools page for the guests

    const STATUS_PENDING   = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_RUNNING   = 2;
    const STATUS_PROCESSED = 3;
    const STATUS_FAILED    = 4;
    const STATUS_CANCELED  = 5;

    const ANONYMIZED_IDENTITY_PREFIX = 'swissup.gdpr.';
    const ANONYMIZED_IDENTITY_SUFFIX = '@example.com';

    /**
     * Array messages, logged during request processing
     *
     * @var array
     */
    private $messages = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Gdpr\Helper\Data $helper,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Gdpr\Model\ResourceModel\ClientRequest::class);
    }

    /**
     * Generate confirmation token
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getConfirmationToken()) {
            $this->setConfirmationToken(bin2hex(random_bytes(32)));
        }
        if (!$this->getWebsiteId()) {
            $this->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());
        }
        if ($this->messages) {
            $this->setReport(implode("\n", $this->messages));
        }
        return parent::beforeSave();
    }

    /**
     * Add processing message
     *
     * @param string $message
     * @return $this
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Add processing error
     *
     * @param string $message
     * @return $this
     */
    public function addError($message)
    {
        $this->messages[] = 'Error: ' . $message;
        return $this;
    }

    /**
     * Add processing success message
     *
     * @param string $message
     * @return $this
     */
    public function addSuccess($message)
    {
        $this->messages[] = 'Success: ' . $message;
        return $this;
    }

    /**
     * Cancel request if it's possible
     *
     * @return boolean
     */
    public function cancel()
    {
        $cancelableStatuses = [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ];

        if (!in_array($this->getStatus(), $cancelableStatuses)) {
            return false;
        }

        $this->setStatus(self::STATUS_CANCELED)->save();

        return true;
    }

    /**
     * Checks if request is confirmed
     *
     * @return boolean
     */
    public function isConfirmed()
    {
        return $this->getStatus() == self::STATUS_CONFIRMED;
    }

    /**
     * Checks if request is failed
     *
     * @return boolean
     */
    public function isFailed()
    {
        return $this->getStatus() == self::STATUS_FAILED;
    }

    /**
     * Checks if request is anonymized
     *
     * @return boolean
     */
    public function isAnonymized()
    {
        return $this->helper->isEmailAnonymized($this->getClientIdentity());
    }

    /**
     * @return array
     */
    public function getAvailableRequestTypes()
    {
        return [
            self::TYPE_DATA_EXPORT => __('Export Data'),
            self::TYPE_DATA_DELETE => __('Delete Data'),
            self::TYPE_DATA_VIEW   => __('View Privacy Tools'),
        ];
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING   => __('Pending'),
            self::STATUS_CONFIRMED => __('Confirmed'),
            self::STATUS_RUNNING   => __('Running'),
            self::STATUS_PROCESSED => __('Processed'),
            self::STATUS_FAILED    => __('Failed'),
            self::STATUS_CANCELED  => __('Canceled'),
        ];
    }
}
