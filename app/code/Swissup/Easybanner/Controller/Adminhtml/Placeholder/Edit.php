<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::easybanner_placeholder';

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
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('placeholder_id');
        $model = $this->_objectManager->create('Swissup\Easybanner\Model\Placeholder');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This placeholder no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Easybanner::easybanner')
            ->addBreadcrumb(__('EasyBanner'), __('EasyBanner'))
            ->addBreadcrumb(__('Placeholders'), __('Placeholders'))
            ->addBreadcrumb(
                $model->getName() ?: __('New Placeholder'),
                $model->getName() ?: __('New Placeholder')
            );

        $resultPage->getConfig()->getTitle()->prepend(__('EasyBanner'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getName() ?: __('New Placeholder'));

        return $resultPage;
    }
}
