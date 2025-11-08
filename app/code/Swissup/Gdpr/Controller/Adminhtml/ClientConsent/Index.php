<?php

namespace Swissup\Gdpr\Controller\Adminhtml\ClientConsent;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\AbstractAction
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::clientconsent';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Gdpr::clientconsent');
        $resultPage->addBreadcrumb(__("Accepted Consents"), __("Accepted Consents"));
        $resultPage->getConfig()->getTitle()->prepend(__("Accepted Consents"));
        return $resultPage;
    }
}
