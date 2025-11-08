<?php
namespace Swissup\Attributepages\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Attributepages::attributepages_page';

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
        $resultPage->setActiveMenu('Swissup_Attributepages::attributepages');
        $resultPage->addBreadcrumb(__('Attribute Pages'), __('Attribute Pages'));
        $resultPage->addBreadcrumb(__('Manage Pages'), __('Manage Pages'));
        $resultPage->getConfig()->getTitle()->prepend(__('Attribute Pages'));

        return $resultPage;
    }
}
