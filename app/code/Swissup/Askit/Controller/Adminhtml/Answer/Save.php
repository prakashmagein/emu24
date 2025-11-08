<?php
namespace Swissup\Askit\Controller\Adminhtml\Answer;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     *
     * @var \Swissup\Askit\Model\MessageRepository
     */
    private $messageRepository;

    /**
     * @param Action\Context $context
     * @param \Swissup\Askit\Model\MessageRepository $messageRepository
     */
    public function __construct(
        Action\Context $context,
        \Swissup\Askit\Model\MessageRepository $messageRepository
    ) {
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
     * Edit Askit item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            if (isset($data['edit']) && is_array($data['edit'])) {
                foreach ($data['edit'] as $field => $allowEdit) {
                    if ($allowEdit === 'true') {
                        continue;
                    }

                    unset($data[$field]);
                }
            }

            /** @var \Swissup\Askit\Model\Message $answer */
            $answer = $this->messageRepository->create();
            $id = $request->getParam('id');
            if ($id) {
                /** @var \Swissup\Askit\Model\Message $answer */
                $answer = $this->messageRepository->get($id);
            } else {
                unset($data['id']);
            }

            /** @var \Swissup\Askit\Model\Message $question */
            $question = $this->messageRepository->getById($data['parent_id']);
            $answer->addData($data)
                ->setStoreId($question->getStoreId())
                ->setItemTypeId($question->getItemTypeId())
                ->setItemId($question->getItemId());

            try {
                $this->messageRepository->save($answer);

                $this->_eventManager->dispatch(
                    'askit_message_after_save',
                    ['message' => $answer, 'request' => $request]
                );
                $this->_eventManager->dispatch(
                    'askit_add_new_answer',
                    ['message' => $answer, 'request' => $request]
                );

                $this->messageManager->addSuccessMessage(__('Answer saved.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $answer->getId(),
                        '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the answer.')
                );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $request->getParam('id')]);
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
