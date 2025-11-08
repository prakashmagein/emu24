<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Prompt;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Swissup\ChatGptAssistant\Model\PromptFactory;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_ChatGptAssistant::save';

    protected PromptFactory $promptFactory;

    protected PageFactory $resultPageFactory;

    protected Registry $registry;

    public function __construct(
        Context $context,
        PromptFactory $promptFactory,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->promptFactory = $promptFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $prompt = $this->promptFactory->create();

        if ($id) {
            $prompt->load($id);
            if (!$prompt->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect  */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->registry->register('chatgpt_assistant_prompt', $prompt);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_ChatGptAssistant::prompt')
            ->addBreadcrumb(__('ChatGPT Assistant'), __('ChatGPT Assistant'))
            ->addBreadcrumb(
                $id ? $prompt->getName() : __('New Prompt'),
                $id ? $prompt->getName() : __('New Prompt')
            );

        $resultPage->getConfig()->getTitle()->prepend(__('Prompts'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? $prompt->getName() : __('New Prompt')
        );

        return $resultPage;
    }
}
