<?php

namespace Swissup\Gdpr\Model;

use Swissup\Gdpr\Model\Config\Source\ClientConsentConfirmationStatuses;

class ClientConsent extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

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
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Gdpr\Model\ResourceModel\ClientConsent::class);
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getWebsiteId()) {
            $this->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());
        }
        return parent::beforeSave();
    }

    public function awaitingConfirmation(): bool
    {
        return $this->getConfirmationStatus() == ClientConsentConfirmationStatuses::PENDING;
    }
}
