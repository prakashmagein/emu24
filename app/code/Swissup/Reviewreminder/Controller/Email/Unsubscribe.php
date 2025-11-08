<?php
namespace Swissup\Reviewreminder\Controller\Email;

use Swissup\Reviewreminder\Model\Entity as ReminderModel;
use Swissup\Reviewreminder\Model\ResourceModel\Entity\CollectionFactory;

class Unsubscribe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Swissup\Reviewreminder\Model\UnsubscribeFactory
     */
    protected $unsubscribeFactory;

    /**
     * @var \Swissup\Reviewreminder\Model\EntityFactory
     */
    protected $reminderFactory;

    /**
     * @var CollectionFactory
     */
    protected $reminderCollectionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Swissup\Reviewreminder\Model\UnsubscribeFactory $unsubscribeFactory
     * @param \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory
     * @param CollectionFactory $reminderCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Swissup\Reviewreminder\Model\UnsubscribeFactory $unsubscribeFactory,
        \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory,
        CollectionFactory $reminderCollectionFactory
    ) {
        $this->unsubscribeFactory = $unsubscribeFactory;
        $this->reminderFactory = $reminderFactory;
        $this->reminderCollectionFactory = $reminderCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $email = null;
        $id = (int)$this->getRequest()->getParam('id');
        $hash = (string)$this->getRequest()->getParam('hash');
        if ($id && $hash) {
            $reminderModel = $this->reminderFactory->create()
                ->load($hash, 'hash');

            if ($id == $reminderModel->getId()) {
                $email = $reminderModel->getCustomerEmail();
            }
        }

        if ($email) {
            try {
                $unsubscribeModel = $this->unsubscribeFactory->create()
                    ->load($email, 'email');

                if (!$unsubscribeModel->getId()) {
                    $unsubscribeModel->setEmail($email)->save();
                    $this->setUnsubscribedStatus($email);
                }

                $this->messageManager->addSuccessMessage(__('You unsubscribed.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while unsubscribing you.')
                );
            }
        }

        return $this->resultRedirectFactory->create()->setPath('/');
    }

    /**
     * Set reminders status to unsubscribed by email
     * @param string $email
     */
    protected function setUnsubscribedStatus($email)
    {
        $reminders = $this->reminderCollectionFactory->create()
            ->addFieldToFilter('customer_email', $email)
            ->load();

        foreach ($reminders as $reminder) {
            $reminder->setStatus(ReminderModel::STATUS_UNSUBSCRIBED)->save();
        }
    }
}
