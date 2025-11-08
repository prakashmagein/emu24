<?php

namespace Swissup\Askit\Controller\Adminhtml\Message;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Swissup\Askit\Model\MessageFactory;
use Swissup\Askit\Model\ResourceModel\Message\CollectionFactory;
use Swissup\Askit\Model\ResourceModel\Message\Collection;

class MassAssignSave extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Askit::message_save';

    /**
     * Field id
     */
    const ID_FIELD = 'main_table.id';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param MessageFactory $messageFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        MessageFactory $messageFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->messageFactory = $messageFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * MassAssignSave action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if ($data) {
            unset($data['selected']);
            unset($data['excluded']);

            $collection = $this->getCollection();

            if (!$collection) {
                $this->messageManager->addErrorMessage(__('No questions to assign for.'));
            }

            foreach ($collection as $message) {
                $newData = ['assign' => []];
                if (isset($data['assign']['products'])) {
                    $products = array_unique(array_merge(
                        $message->getAssignProducts(),
                        explode('&', $data['assign']['products'])
                    ));
                    $newData['assign']['products'] = implode('&', $products);
                }

                if (isset($data['assign']['categories'])) {
                    $categories = array_unique(array_merge(
                        $message->getAssignCategories(),
                        explode('&', $data['assign']['categories'])
                    ));
                    $newData['assign']['categories'] = implode('&', $categories);
                }

                if (isset($data['assign']['pages'])) {
                    $pages = array_unique(array_merge(
                        $message->getAssignPages(),
                        explode('&', $data['assign']['pages'])
                    ));
                    $newData['assign']['pages'] = implode('&', $pages);
                }

                $message->addData($newData);

                try {
                    $message->save();
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the question.')
                    );
                }
            }

            $this->messageManager->addSuccessMessage(
                __('Assignment completed successfully.')
            );
        }

        return $resultRedirect->setPath('*/question/');
    }

    /**
     * @return Collection|null
     */
    protected function getCollection()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $selected = $request->getParam('selected');
        $excluded = $request->getParam('excluded');
        if (!$selected && !$excluded) {
            return null;
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);

        return $collection;
    }
}
