<?php
namespace Swissup\Askit\Controller\Question;

class Save extends \Swissup\Askit\Controller\Message\SaveAbstract
{
    /**
     * Post user question
     *
     * @inherit
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
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

        $isLoggedIn = $this->customerSession->isLoggedIn();
        $customer = $this->customerSession->getCustomer();
        $isAllowedGuestQuestion = $this->configHelper->isAllowedGuestQuestion();

        if (!$isLoggedIn && !$isAllowedGuestQuestion) {
            $this->messageManager->addErrorMessage(__('Your must login'));
            return $isAjax
                ? $this->resultJson()
                : $this->redirectReferer();
        }

        try {
            $post['email'] = $isLoggedIn ? $customer->getEmail() : $post['email'];
            $post['customer_id'] = $isLoggedIn ? $customer->getId() : null;
            $post['store_id'] = $this->storeManager->getStore()->getId();
            $post['status'] = $this->configHelper->getDefaultQuestionStatus();

            $this->validateData($post);

            $model = $this->messageRepository->create();
            $model->setData($post);
            $this->messageRepository->save($model);

            $this->eventManager->dispatch(
                'askit_message_after_save',
                ['message' => $model, 'request' => $this->getRequest()]
            );

            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your question. We\'ll respond to you very soon.')
            );
        } catch (\Exception $e) {
            // $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage(
                $e->getMessage()  . ' '. __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }

        return $isAjax
            ? $this->resultJson()
            : $this->redirectReferer();
    }
}
