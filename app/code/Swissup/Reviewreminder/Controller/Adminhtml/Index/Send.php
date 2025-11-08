<?php
namespace Swissup\Reviewreminder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Send extends Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_Reviewreminder::send';
    /**
     * Reminder helper
     *
     * @var Swissup\Reviewreminder\Helper\Helper
     */
    protected $helper;
    /**
     * @param Action\Context $context
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     */
    public function __construct(
        Action\Context $context,
        \Swissup\Reviewreminder\Helper\Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Send action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                $this->helper->sendReminders([$id]);
                $this->messageManager->addSuccess(__('Reminder has been sent.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while sending the reminder.'));
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
            }
        }
        $this->messageManager->addError(__('Unable to find a reminder to send.'));
        return $resultRedirect->setPath('*/*/');
    }
}
