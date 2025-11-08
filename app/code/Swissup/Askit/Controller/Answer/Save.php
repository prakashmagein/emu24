<?php
namespace Swissup\Askit\Controller\Answer;

class Save extends \Swissup\Askit\Controller\Message\SaveAbstract
{
    /**
     * Post user question
     *
     * @inherit
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http|\Laminas\Http\Request $request */
        $request = $this->getRequest();
        $isAjax = $request->isAjax();
        if ($this->messageManager->getMessages()->getErrors()
            || !$this->validateFormKey()
        ) {
            return $isAjax
                ? $this->resultJson()
                : $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $post = $request->getPostValue();
        $isEnabled = $this->configHelper->isEnabled();

        if (!$post || !$isEnabled) {
            return $isAjax
                ? $this->resultJson()
                : $this->redirectReferer();
        }

        try {
            $isLoggedIn = $this->customerSession->isLoggedIn();
            $customer = $this->customerSession->getCustomer();

            /** @var \Swissup\Askit\Model\Message $question */
            $question = $this->messageRepository->getById($post['parent_id']);

            $post['item_id'] = $question->getItemId();
            $post['item_type_id'] = $question->getItemTypeId();
            $post['customer_id'] = $isLoggedIn ? $customer->getId() : null;
            $post['customer_name'] = $isLoggedIn ? $customer->getName() : $post['customer_name'];
            $post['email'] = $isLoggedIn ? $customer->getEmail() : $post['email'];

            $post['store_id'] = $this->storeManager->getStore()->getId();

            $post['status'] = $this->configHelper->getDefaultAnswerStatus();
            $post['hint'] = 0;

            $this->validateData($post);

            $model = $this->messageRepository->create();
            $model->setData($post);

            $this->messageRepository->save($model);

            $this->eventManager->dispatch(
                'askit_message_after_save',
                ['message' => $model, 'request' => $request]
            );

            $this->messageManager->addSuccessMessage(
                __('Thanks for your comment. We\'ll respond to you very soon.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()  . ' '
                    . __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }

        return $isAjax
                ? $this->resultJson()
                : $this->redirectReferer();
    }
}
