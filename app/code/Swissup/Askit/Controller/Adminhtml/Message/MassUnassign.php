<?php

namespace Swissup\Askit\Controller\Adminhtml\Message;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Swissup\Askit\Model\MessageFactory;
use Swissup\Askit\Model\ResourceModel\Message\CollectionFactory;
use Swissup\Askit\Model\ResourceModel\Message\Collection;

class MassUnassign extends MassAssignSave
{
    /**
     * MassUnassign action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        $entity = $this->getRequest()->getParam('entity', '');

        $collection = $this->getCollection();

        if (!$collection) {
            $this->messageManager->addErrorMessage(__('No questions to unassign.'));
        }

        $newData = [
            'assign' => [
                $entity => ''
            ]
        ];
        foreach ($collection as $message) {
            $message->addData($newData);

            try {
                $message->save();
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
        }

        $this->messageManager->addSuccessMessage(__('Unassignment completed successfully.'));

        return $resultRedirect->setPath('*/question/');
    }
}
