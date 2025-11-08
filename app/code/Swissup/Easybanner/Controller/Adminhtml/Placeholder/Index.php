<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
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
        $resultPage->setActiveMenu('Swissup_Easybanner::easybanner_placeholder');
        $resultPage->addBreadcrumb(__('EasyBanner'), __('EasyBanner'));
        $resultPage->addBreadcrumb(__('Placeholders'), __('Placeholders'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Placeholders'));

        return $resultPage;
    }
}
