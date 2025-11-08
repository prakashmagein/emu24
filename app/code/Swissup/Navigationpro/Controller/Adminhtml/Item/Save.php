<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Item;

use Swissup\Navigationpro\Model\ItemFactory;
use Magento\Store\Model\Store;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::item_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var MenuFactory
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
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id      = $this->getRequest()->getParam('item_id');
        $menuId  = $this->getRequest()->getParam('menu_id');
        $storeId = $this->getRequest()->getParam('store_id', Store::DEFAULT_STORE_ID);

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/menu/edit', ['menu_id' => $menuId]);
        }

        if (isset($data['is_active']) && $data['is_active'] === 'true') {
            $data['is_active'] = 1;
        }
        if (empty($data['item_id'])) {
            $data['item_id'] = null;
        }
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        /** @var \Swissup\Navigationpro\Model\Item $item */
        $item = $this->itemFactory->create();
        if ($id) {
            $item->setStoreId($storeId)->load($id);
        } else {
            $data['store_id'] = Store::DEFAULT_STORE_ID;
        }

        if (!$item->getId() && $id) {
            $this->messageManager->addError(__('This item no longer exists.'));
            return $resultRedirect->setPath('*/menu/edit', [
                'menu_id' => $menuId,
                'store'   => $storeId,
            ]);
        }

        if (!empty($data['dropdown_settings']['use_menu_settings'])
            && !$item->getData('dropdown_settings/layout')
        ) {
            // if item uses menu settings and  no layout is DB - do not save settings
            // This will allow to show settings inherited from menu when custmer
            // toggles 'use_menu_settings'
            $data['dropdown_settings'] = null;
        }

        $useDefault = $this->getRequest()->getParam('use_default', []);
        foreach ($useDefault as $key => $flag) {
            $flag = (int)$flag;
            if (!$flag) {
                continue;
            }

            if (isset($data[$key])) {
                $data[$key] = null;
            } elseif (isset($data['dropdown_settings'][$key])) {
                $data['dropdown_settings'][$key] = null;
            }
        }

        $item->addData($data);

        try {
            $item->save();
            $this->messageManager->addSuccess(__('You saved item.'));
            $this->dataPersistor->clear('navigationpro_item');
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving item.'));
        }

        $this->dataPersistor->set('navigationpro_item', $data);

        return $resultRedirect->setPath('*/menu/edit', [
            'item_id' => $item->getId(),
            'menu_id' => $item->getMenuId(),
            'store'   => $storeId,
        ]);
    }
}
