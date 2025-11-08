<?php

namespace Swissup\Askit\Controller\Adminhtml\Answer;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Askit::message_save';

    /**
     * @var \Swissup\Askit\Api\MessageRepositoryInterface
     */
    private $answerRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\Askit\Api\MessageRepositoryInterface $answerRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Askit\Api\MessageRepositoryInterface $answerRepository
    ) {
        parent::__construct($context);
        $this->answerRepository = $answerRepository;
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

        $id = $this->getRequest()->getParam('id');
        $togridFlag = $this->getRequest()->getParam('togrid');
        if ($id) {
            try {
                $answer = $this->answerRepository->getById($id);
                $questionId = $answer->getParentId();
                $this->answerRepository->delete($answer);

                $this->messageManager->addSuccessMessage(__('The answer has been deleted.'));
                if ($togridFlag) {
                    return $resultRedirect->setPath('*/*/');
                }

                return $resultRedirect->setPath('askit/question/edit', ['id' => $questionId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['answer_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an answer to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
