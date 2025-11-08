<?php

namespace Swissup\Gdpr\Controller\Adminhtml\ClientRequest;

use Swissup\Gdpr\Model\ClientRequest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory;

class MassCancel extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::clientrequest_cancel';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
            if (!$item->cancel()) {
                $skipped++;
            }
        }

        if ($collectionSize - $skipped) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been canceled.', $collectionSize - $skipped)
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
