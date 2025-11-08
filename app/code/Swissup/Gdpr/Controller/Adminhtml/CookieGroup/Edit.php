<?php

namespace Swissup\Gdpr\Controller\Adminhtml\CookieGroup;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Swissup\Gdpr\Model\CookieGroupFactory;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @var CookieGroupFactory
     */
    protected $groupFactory;

    /**
     * @param Context $context
     * @param CookieGroupFactory $groupFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        CookieGroupFactory $groupFactory
    ) {
        parent::__construct($context);
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        $code = $this->getRequest()->getParam('code');
        $group = $this->groupFactory->create();
        $title = $code ?: __('New Group');

        if ($id) {
            $group->load($id);
            if (!$group->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect  */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $title = $group->getTitle();
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Swissup_Gdpr::cookieregistry')
            ->addBreadcrumb(__('Cookie Groups'), __('Cookie Groups'))
            ->addBreadcrumb($title, $title);

        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Groups'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        if (!$id) {
            $resultPage->getConfig()->addBodyClass('gdpr-storeview-hidden');
        }

        return $resultPage;
    }
}
