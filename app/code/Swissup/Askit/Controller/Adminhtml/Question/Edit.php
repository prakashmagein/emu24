<?php

namespace Swissup\Askit\Controller\Adminhtml\Question;

class Edit extends \Swissup\Askit\Controller\Adminhtml\Message\AbstractEdit
{
    const ADMIN_RESOURCE = 'Swissup_Askit::message_save';

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $question = $this->messageRepository->create();

        if ($id) {
            $question = $this->messageRepository->getById($id);
            if (!$question->getId() || $question->getParentId()) {
                return $this->itemNotExist();
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $title = $this->getShortenedText($question->getText());
        $resultPage->setActiveMenu('Swissup_Askit::question')
            ->addBreadcrumb(__('Askit'), __('Askit'))
            ->addBreadcrumb(
                $id ? $title : __('New Item'),
                $id ? $title : __('New Item')
            );

        $resultPage->getConfig()->getTitle()->prepend(__('Askit'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? $title : __('New Item')
        );

        return $resultPage;
    }
}
