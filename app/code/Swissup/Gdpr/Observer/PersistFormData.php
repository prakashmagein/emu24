<?php

namespace Swissup\Gdpr\Observer;

class PersistFormData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $reviewSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Session\Generic $reviewSession
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\Generic $reviewSession,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->customerSession = $customerSession;
        $this->reviewSession = $reviewSession;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form = $observer->getForm();
        $supportedFormIds = [
            'magento:customer-registration',
            'magento:contact-us',
            'magento:product-review',
        ];

        if (!in_array($form->getId(), $supportedFormIds)) {
            return;
        }

        // Save post values into appropriate object to restore entered values
        // after redirect
        $data = $this->request->getPostValue();
        switch ($form->getId()) {
            case 'magento:customer-registration':
                $this->customerSession->setCustomerFormData($data);
                break;
            case 'magento:contact-us':
                $this->dataPersistor->set('contact_us', $data);
                break;
            case 'magento:product-review':
                $this->reviewSession->setFormData($data);
                break;
        }
    }
}
