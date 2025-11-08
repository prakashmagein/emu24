<?php
namespace Swissup\Askit\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Swissup\Askit\Model\MessageRepository
     */
    private $messageRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Askit\Model\MessageRepository $messageRepository
    )
    {
        parent::__construct($context);
        $this->messageRepository = $messageRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_Askit::message_save');
    }

    /**
     * Delete Askit item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        // check if we know what should be deleted
        $id = $request->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->messageRepository->create();
            try {
                // init model and delete
                $this->messageRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccessMessage(__('The question has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'askit_message_prepare_on_delete',
                    ['message' => $model, 'status' => 'success']
                );
                return $resultRedirect->setPath('askit/question/index');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'askit_message_prepare_on_delete',
                    ['message' => $model, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a question to delete.'));
        // go to grid
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
