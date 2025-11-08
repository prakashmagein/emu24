<?php

namespace Swissup\Gdpr\Controller\Adminhtml\BlockedCookie;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\BlockedCookie\CollectionFactory;
use Swissup\Gdpr\Model\ResourceModel\Cookie\MergedCollectionFactory;

class MassRegister extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MergedCollectionFactory
     */
    protected $cookieCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param MergedCollectionFactory $cookieCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        MergedCollectionFactory $cookieCollectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->cookieCollectionFactory = $cookieCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $counter = 0;
        $group = $this->getRequest()->getParam('group');
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $cookieCollection = $this->cookieCollectionFactory->create()
            ->addFieldToFilter('name', ['in' => $collection->getColumnValues('name')]);

        foreach ($collection->getItems() as $item) {
            $cookie = $cookieCollection->getItemByColumnValue('name', $item->getName());

            if (!$cookie) {
                $cookie = $cookieCollection->getNewEmptyItem();
            }

            $cookie->setName($item->getName())
                ->setGroup($group)
                ->setStatus(1)
                ->save();

            $counter++;

            $item->delete();
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been created.', $counter)
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/cookie/');
    }
}
