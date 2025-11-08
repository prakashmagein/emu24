<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Prompt;

use Magento\Backend\App\Action\Context;
use Swissup\ChatGptAssistant\Model\PromptFactory;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_ChatGptAssistant::delete';

    protected PromptFactory $promptFactory;

    public function __construct(
        Context $context,
        PromptFactory $promptFactory
    ) {
        $this->promptFactory = $promptFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $model = $this->promptFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the item.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an item to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
