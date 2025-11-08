<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Framework\Controller\ResultFactory;

class BuiltIn extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Swissup_Gdpr::cookieregistry');
        $resultPage->addBreadcrumb(__('Built-in Cookies'), __('Built-in Cookies'));
        $resultPage->getConfig()->getTitle()->prepend(__('Built-in Cookies'));
        return $resultPage;
    }
}
