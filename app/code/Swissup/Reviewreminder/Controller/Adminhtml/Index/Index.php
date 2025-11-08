<?php
namespace Swissup\Reviewreminder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Reviewreminder::reviewreminder';

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
        $resultPage->setActiveMenu('Swissup_Reviewreminder::reviewreminder');
        $resultPage->addBreadcrumb(__('Review Reminders'), __('Review Reminders'));
        $resultPage->addBreadcrumb(__('Manage Reminders'), __('Manage Reminders'));
        $resultPage->getConfig()->getTitle()->prepend(__('Review Reminders'));

        return $resultPage;
    }
}
