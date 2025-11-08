<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Swissup_Gdpr::cookieregistry');
        $resultPage->addBreadcrumb(__('Cookie Registry'), __('Cookie Registry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Registry'));
        return $resultPage;
    }
}
