<?php

namespace Swissup\Pagespeed\Controller\Adminhtml\Criticalcss;

use Magento\Backend\App\Action\Context;
use Swissup\License\Model\ActivationFactory;

class GenerateDefault extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Pagespeed::generate';

    /**
     * @var \Swissup\Pagespeed\Model\Css\GetCriticalCss
     */
    private $service;

    public function __construct(
        Context $context,
        \Swissup\Pagespeed\Model\Css\GetCriticalCss $service
    ) {
        parent::__construct($context);
        $this->service = $service;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $this->service
                ->setStore($storeId)
                ->generateDefault()
                ->saveConfig();
            $this->messageManager->addSuccess(
                __('Critical css generated, saved and enabled in config.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
