<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Prompt;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Swissup\ChatGptAssistant\Model\PromptFactory;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_ChatGptAssistant::save';

    protected DataPersistorInterface $dataPersistor;

    protected PromptFactory $promptFactory;

    public function __construct(
        Context $context,
        PromptFactory $promptFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->promptFactory = $promptFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }

            $id = $this->getRequest()->getParam('entity_id');
            $prompt = $this->promptFactory->create()->load($id);
            if (!$prompt->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This prompt no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $prompt->addData($data);

            try {
                $prompt->save();
                $this->messageManager->addSuccessMessage(__('You saved the prompt.'));
                $this->dataPersistor->clear('chatgpt_assistant_prompt');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $prompt->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e,
                    __('Something went wrong while saving the prompt data. Please review the error log.')
                );
            }

            $this->dataPersistor->set('chatgpt_assistant_prompt', $data);

            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
