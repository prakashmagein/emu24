<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Easybanner::placeholder_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor
    ) {
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
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('placeholder_id');
            /** @var \Swissup\Easybanner\Model\Placeholder $model */
            $model = $this->_objectManager->create('Swissup\Easybanner\Model\Placeholder');

            if (empty($data['placeholder_id'])) {
                $data['placeholder_id'] = null;
            }

            if ($id) {
                $model->load($id);
            }

            $model->addData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Placeholder has been saved.'));
                $this->dataPersistor->clear('easybanner_placeholder');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'placeholder_id' => $model->getId(),
                        '_current' => true
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the placeholder.'));
            }

            $this->dataPersistor->set('easybanner_placeholder', $data);
            return $resultRedirect->setPath('*/*/edit', [
                'placeholder_id' => $this->getRequest()->getParam('placeholder_id')
            ]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
