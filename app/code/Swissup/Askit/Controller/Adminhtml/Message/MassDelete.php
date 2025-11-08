<?php
namespace Swissup\Askit\Controller\Adminhtml\Message;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Swissup\Askit\Controller\Adminhtml\AbstractMassStatus
{
    /**
     * @var string
     */
    private $repositoryClass = \Swissup\Askit\Model\MessageRepository::class;

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

        try {
            if (isset($excluded)) {
                if (!empty($excluded) && 'false' != $excluded) {
                    $this->excludedDelete($excluded);
                } else {
                    $this->deleteAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedDelete($selected);
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
     * Delete all
     *
     * @return void
     * @throws \Exception
     */
    protected function deleteAll()
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();

        $this->setSuccessMessage($this->delete($collection));
    }

    /**
     * Delete all but the not selected
     *
     * @param array $excluded
     * @return void
     * @throws \Exception
     */
    protected function excludedDelete(array $excluded)
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setSuccessMessage($this->delete($collection));
    }

    /**
     * Delete selected items
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedDelete(array $selected)
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setSuccessMessage($this->delete($collection));
    }

    /**
     * Delete collection items
     *
     * @param \Swissup\Askit\Model\ResourceModel\Message\Collection $collection
     * @return int
     */
    protected function delete(\Swissup\Askit\Model\ResourceModel\Message\Collection $collection)
    {
        $count = 0;
        /** @var \Swissup\Askit\Model\MessageRepository $repository */
        $repository = $this->_objectManager->get($this->repositoryClass);
        foreach ($collection->getAllIds() as $id) {
            /** @var \Swissup\Askit\Api\Data\MessageInterface $model */
            $model = $repository->get($id);
            $repository->delete($model);
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
            __('A total of %1 record(s) have been deleted.', $count)
        );
    }
}
