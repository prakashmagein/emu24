<?php
namespace Swissup\Reviewreminder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Swissup\Reviewreminder\Model\ResourceModel\Entity\CollectionFactory;

/**
 * Class MassSend
 */
class MassSend extends \Magento\Backend\App\Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_Reviewreminder::send';

    protected Filter $filter;

    protected CollectionFactory $collectionFactory;

    protected \Swissup\Reviewreminder\Helper\Helper $helper;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Swissup\Reviewreminder\Helper\Helper $helper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $remindersIds = $collection->getAllIds();
        try {
            $this->helper->sendReminders($remindersIds);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while sending the reminder.'));

            return $resultRedirect->setPath('*/*/');
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 reminders have been sent.', count($remindersIds))
        );

        return $resultRedirect->setPath('*/*/');
    }
}
