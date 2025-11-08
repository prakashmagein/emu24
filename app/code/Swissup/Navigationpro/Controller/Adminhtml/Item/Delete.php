<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Item;

use Swissup\Navigationpro\Model\ItemFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::item_delete';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->itemFactory = $itemFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('item_id');
        if ($id) {
            $model = $this->itemFactory->create();
            $model->load($id);

            if ($model->getId()) {
                $menuId = $model->getMenuId();
                $parentId = $model->getParentItem()->getId();
                try {
                    $model->delete();
                    $this->messageManager->addSuccess(__('You deleted the item.'));
                    return $resultRedirect->setPath('*/menu/edit', [
                        'menu_id' => $menuId,
                        'item_id' => $parentId,
                    ]);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    return $resultRedirect->setPath('*/menu/edit', [
                        'item_id' => $id,
                        'menu_id' => $menuId,
                    ]);
                }
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));

        return $resultRedirect->setPath('*/menu/');
    }
}
