<?php

namespace Swissup\Gdpr\Observer;

class RegisterPersonalDataHandlers implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Prepare personal data handlers
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getCollection();
        $handlers = [
            \Swissup\Gdpr\Model\PersonalDataHandler\Customer::class,
            \Swissup\Gdpr\Model\PersonalDataHandler\ProductReview::class,
            \Swissup\Gdpr\Model\PersonalDataHandler\Newsletter::class,
            \Swissup\Gdpr\Model\PersonalDataHandler\Quote::class,
            \Swissup\Gdpr\Model\PersonalDataHandler\Sales::class,
        ];

        foreach ($handlers as $className) {
            $collection->addItem($this->objectManager->create($className));
        }
    }
}
