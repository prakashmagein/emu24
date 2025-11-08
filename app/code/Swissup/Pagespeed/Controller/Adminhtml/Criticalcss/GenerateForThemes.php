<?php

namespace Swissup\Pagespeed\Controller\Adminhtml\Criticalcss;

use Magento\Backend\App\Action\Context;
use Swissup\License\Model\ActivationFactory;

class GenerateForThemes extends \Magento\Backend\App\Action
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
            $request =  $this->getRequest();
            $websiteId = (int) $request->getParam('website', 0);
            $groupId = (int) $request->getParam('group', 0);
            $storeId = (int) $request->getParam('store', 0);
            $this->service
                ->setWebsite($websiteId)
                ->setGroup($groupId)
                ->setStore($storeId)
                ->generateForThemes();
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
