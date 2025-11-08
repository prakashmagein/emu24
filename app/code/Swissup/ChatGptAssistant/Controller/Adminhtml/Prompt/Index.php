<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Prompt;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_ChatGptAssistant::prompt';

    protected PageFactory $resultPageFactory;

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
        $resultPage->setActiveMenu('Swissup_ChatGptAssistant::prompt');
        $resultPage->addBreadcrumb(__('ChatGPT Assistant'), __('ChatGPT Assistant'));
        $resultPage->addBreadcrumb(__('Manage Prompts'), __('Manage Prompts'));
        $resultPage->getConfig()->getTitle()->prepend(__('ChatGPT Prompts'));

        return $resultPage;
    }
}
