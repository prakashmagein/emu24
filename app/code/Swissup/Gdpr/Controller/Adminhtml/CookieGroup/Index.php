<?php

namespace Swissup\Gdpr\Controller\Adminhtml\CookieGroup;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
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
        $resultPage->addBreadcrumb(__('Cookie Groups'), __('Cookie Groups'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Groups'));
        return $resultPage;
    }
}
