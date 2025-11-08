<?php
namespace Swissup\Askit\Controller\Adminhtml\Message;

use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Controller\Adminhtml\AbstractMassStatus;
use Magento\Framework\Controller\ResultFactory;
use Swissup\Askit\Model\ResourceModel\Message\Collection as AbstractCollection;

/**
 * Class MassStatus
 */
class MassStatus extends AbstractMassStatus//\Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $repositoryClass = \Swissup\Askit\Model\MessageRepository::class;

    /**
     * item status
     * @var int
     */
    protected $itemStatus = MessageInterface::STATUS_PENDING;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');

        $this->itemStatus = (int) $this->getRequest()->getParam('change_status', false);
        try {
            if (isset($excluded)) {
                if (!empty($excluded) && 'false' != $excluded) {
                    $this->excludedChange($excluded);
                } else {
                    $this->changeAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedChange($selected);
            } else {
                $this->messageManager->addErrorMessage(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     *
     * @return void
     * @throws \Exception
     */
    protected function changeAll()
    {
        /** @var AbstractCollection $collection */
        $collection = $this->getCollection();
        $this->setSuccessMessage($this->change($collection));
    }

    /**
     *
     * @param array $excluded
     * @return void
     * @throws \Exception
     */
    protected function excludedChange(array $excluded)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setSuccessMessage($this->change($collection));
    }

    /**
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedChange(array $selected)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setSuccessMessage($this->change($collection));
    }

    /**
     *
     * @param AbstractCollection $collection
     * @return int
     */
    protected function change(AbstractCollection $collection)
    {
        $count = 0;
        /** @var \Swissup\Askit\Model\MessageRepository $repository */
        $repository = $this->_objectManager->get($this->repositoryClass);
        foreach ($collection->getAllIds() as $id) {
            /** @var \Swissup\Askit\Model\Message $model */
            $model = $repository->getById($id);
            $model->setStatus($this->itemStatus);
            $repository->save($model);
            ++$count;
        }

        return $count;
    }

    /**
     * Set error messages
     *
     * @param int $count
     * @return void
     */
    protected function setSuccessMessage($count)
    {
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been changed.', $count)
        );
    }
}
