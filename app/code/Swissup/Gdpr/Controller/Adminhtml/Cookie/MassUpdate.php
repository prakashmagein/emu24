<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\Cookie\CustomCollectionFactory;

class MassUpdate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CustomCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CustomCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CustomCollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $key = $this->getRequest()->getParam('field');
        $value = $this->getRequest()->getParam('value');

        foreach ($collection as $item) {
            $item->setData($key, $value)->save();
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $collection->getSize())
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
