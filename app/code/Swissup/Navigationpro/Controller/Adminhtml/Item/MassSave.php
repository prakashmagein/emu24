<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Item;

use Magento\Store\Model\Store;
use Magento\Backend\App\Action\Context;
use Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;

class MassSave extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::item_save';

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @param Context $context
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        Context $context,
        ItemCollectionFactory $itemCollectionFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
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

        $itemIds = $this->getRequest()->getParam('item_ids', []);
        $menuId = $this->getRequest()->getParam('menu_id');
        $storeIds = $this->getRequest()->getParam('store_ids', [Store::DEFAULT_STORE_ID]);

        $rawData = $this->getRequest()->getPostValue();
        if (!$rawData) {
            return $resultRedirect->setPath('*/menu/edit', ['menu_id' => $menuId]);
        }

        $data = [];
        foreach ($rawData['use_default'] as $key => $flag) {
            if ($flag || !isset($rawData[$key])) {
                continue;
            }

            $pos = strpos($key, 'dropdown_');
            if ($pos !== false) {
                $newKey = substr_replace($key, '', $pos, strlen('dropdown_'));
                $data['dropdown_settings'][$newKey] = $rawData[$key];
            } else {
                $data[$key] = $rawData[$key];
            }
        }

        if (isset($data['is_active']) && $data['is_active'] === 'true') {
            $data['is_active'] = 1;
        }

        $savedIds = [];
        $failedIds = [];
        foreach ($storeIds as $storeId) {
            $collection = $this->itemCollectionFactory->create();
            $collection->setStoreId($storeId)
                ->canUseFallbackStoreId(false)
                ->addFieldToFilter('item_id', ['in' => $itemIds]);

            foreach ($collection as $item) {
                $item->setStoreId($storeId);

                if (!empty($data['dropdown_settings'])) {
                    if (!empty($data['dropdown_settings']['use_menu_settings'])) {
                        $data['dropdown_settings'] = [
                            'use_menu_settings' => 1,
                        ];
                    }

                    $dropdownSettings = $item->getData('dropdown_settings');
                    if ($dropdownSettings) {
                        $dropdownSettings = array_filter($dropdownSettings);
                        $data['dropdown_settings'] = array_merge(
                            $dropdownSettings,
                            $data['dropdown_settings']
                        );
                    }
                }

                $item->addData($data);

                try {
                    $item->save();
                    $savedIds[$item->getId()] = true;
                } catch (\Exception $e) {
                    $failedIds[$item->getId()] = $e->getMessage();
                }
            }
        }

        if ($savedIds) {
            $this->messageManager->addSuccess(__(
                '%1 item(s) was saved successfully',
                count($savedIds)
            ));
        }

        if ($failedIds) {
            $this->messageManager->addError(__(
                '%1 item(s) was not saved. Messages:<br/>%2',
                count($failedIds),
                implode('<br/>', $failedIds)
            ));
        }

        return $resultRedirect->setPath('*/menu/edit', [
            'item_id' => $item->getId(),
            'menu_id' => $item->getMenuId(),
            'store'   => $this->getRequest()->getParam('store_id'),
        ]);
    }
}
