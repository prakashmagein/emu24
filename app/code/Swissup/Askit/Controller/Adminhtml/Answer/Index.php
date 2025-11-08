<?php
namespace Swissup\Askit\Controller\Adminhtml\Answer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Swissup_Askit::question';

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
        $resultPage->setActiveMenu('Swissup_Askit::question');
        $resultPage->addBreadcrumb(__('Askit Recent Answers'), __('Askit Recent Answers'));
        $resultPage->addBreadcrumb(__('Manage Recent Askit Answers'), __('Manage Recent Askit Answers'));
        $resultPage->getConfig()->getTitle()->prepend(__('Askit Recent Answers'));

        return $resultPage;
    }
}
