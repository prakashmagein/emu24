<?php
namespace Swissup\Askit\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Swissup\Askit\Model\MessageRepository
     */
    protected $messageRepository;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @param Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Swissup\Askit\Model\MessageRepository $messageRepository
     */
    public function __construct(
        Action\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Swissup\Askit\Model\MessageRepository $messageRepository
    ) {
        parent::__construct($context);

        $this->backendAuthSession = $backendAuthSession;
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
            /** @var \Swissup\Askit\Model\Message $model */
            $model = $this->messageRepository->create();

            $id = $request->getParam('id');
            if ($id) {
                /** @var \Swissup\Askit\Model\Message $model */
                $model = $this->messageRepository->get($id);
            }

            // Unset data that can not be modified.
            if (isset($data['edit']) && is_array($data['edit'])) {
                foreach ($data['edit'] as $field => $allowEdit) {
                    if ($allowEdit === 'true') {
                        continue;
                    }

                    unset($data[$field]);
                }
            }

            $model->addData($data);

            try {
                $this->messageRepository->save($model);

                $this->_eventManager->dispatch(
                    'askit_message_after_save',
                    ['message' => $model, 'request' => $request]
                );

                $this->messageManager->addSuccessMessage(__('You question saved.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                if (!empty($data['answer'])) {
                    /** @var \Swissup\Askit\Model\Message $answer */
                    $answer = $this->messageRepository->create();
                    $adminUser = $this->backendAuthSession->getUser();

                    $answer
                        ->setParentId($model->getId())
                        ->setStatus(\Swissup\Askit\Model\Message::STATUS_APPROVED)
                        ->setStoreId($model->getStoreId())
                        ->setText($data['answer'])
                        ->setItemTypeId($model->getItemTypeId())
                        ->setItemId($model->getItemId())
                        ->setHint(0)
                        // ->setCustomerName('admin')
                        ->setCustomerName($adminUser->getFirstname() . ' ' . $adminUser->getLastname())
                        ->setEmail($adminUser->getEmail());
                    $this->messageRepository->save($answer);

                    $this->_eventManager->dispatch(
                        'askit_add_new_answer',
                        ['message' => $model, 'request' => $request]
                    );

                    $model->setStatus(\Swissup\Askit\Model\Message::STATUS_APPROVED);
                    $this->messageRepository->save($model);

                    $this->messageManager->addSuccessMessage(__('You answer was added.'));
                }

                if ($request->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the question.')
                );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $request->getParam('id')]
            );
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
