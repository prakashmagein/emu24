<?php

namespace Swissup\Gdpr\Controller\Adminhtml\ClientRequest;

use Swissup\Gdpr\Model\ClientRequest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory;

class MassProcess extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::clientrequest_process';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Swissup\Gdpr\Model\ClientRequest\Processor
     */
    private $processor;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Swissup\Gdpr\Model\ClientRequest\Processor $processor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Swissup\Gdpr\Model\ClientRequest\Processor $processor
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->processor = $processor;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $skipped = 0;
        foreach ($collection->getItems() as $item) {
            if (!$this->processor->canProcess($item)) {
                $skipped++;
                continue;
            }
            $this->processor->process($item);
        }

        if ($collectionSize - $skipped) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been processed.', $collectionSize - $skipped)
            );
        } else {
            $this->messageManager->addNotice(
                __('A total of %1 record(s) have been skipped', $skipped)
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
