<?php

namespace Swissup\Gdpr\Controller\Adminhtml\ClientRequest;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

class Process extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::clientrequest_process';

    /**
     * @var \Swissup\Gdpr\Model\ClientRequestFactory
     */
    private $factory;

    /**
     * @var \Swissup\Gdpr\Model\ClientRequest\Processor
     */
    private $processor;

    /**
     * @param Context $context
     * @param \Swissup\Gdpr\Model\ClientRequestFactory $factory
     * @param \Swissup\Gdpr\Model\ClientRequest\Processor $processor
     */
    public function __construct(
        Context $context,
        \Swissup\Gdpr\Model\ClientRequestFactory $factory,
        \Swissup\Gdpr\Model\ClientRequest\Processor $processor
    ) {
        $this->factory = $factory;
        $this->processor = $processor;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $item = $this->factory->create();
        $item->load($this->getRequest()->getPost('id'));

        if (!$item->getId()) {
            $this->messageManager->addError(__('This item no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        if (!$this->processor->canProcess($item)) {
            $this->messageManager->addNotice(__('Requested item was skipped.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->processor->process($item);

        if ($item->getStatus() == \Swissup\Gdpr\Model\ClientRequest::STATUS_PROCESSED) {
            $this->messageManager->addSuccess(__('Request was successfully processed.'));
        } else {
            $this->messageManager->addError(__('Request processing was failed.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
