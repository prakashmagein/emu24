<?php

namespace Swissup\Askit\Controller\Adminhtml\Message;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\View\Result\PageFactory;
use Swissup\Askit\Model\ResourceModel\Message\CollectionFactory;

class MassAssign extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Askit::message_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        // $selected = $this->getRequest()->getParam('selected');
        // $excluded = $this->getRequest()->getParam('excluded');
        // echo '<pre>';
        // var_dump($selected);
        // var_dump($excluded);
        // echo '</pre>';
        // die;

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Askit::question')
            ->addBreadcrumb(__('Askit'), __('Askit'))
            ->addBreadcrumb(__('Assign to...'), __('Assign to...'));

        $resultPage->getConfig()->getTitle()->prepend(__('Askit'));
        $resultPage->getConfig()->getTitle()->prepend(__('Assign to ...'));

        return $resultPage;
    }
}
