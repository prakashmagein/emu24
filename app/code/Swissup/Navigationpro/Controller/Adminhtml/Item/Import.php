<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Item;

use Swissup\Navigationpro\Model\Item;
use Swissup\Navigationpro\Model\MenuFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class Import extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::item_save';

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @param Context $context
     * @param ItemFactory $menuFactory
     */
    public function __construct(
        Context $context,
        MenuFactory $menuFactory
    ) {
        $this->menuFactory = $menuFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $mode       = $this->getRequest()->getParam('mode');
        $entityId   = $this->getRequest()->getParam('remote_entity_id');
        $entityType = $this->getRequest()->getParam('remote_entity_type');
        $menuId     = $this->getRequest()->getParam('menu_id');
        $parentId   = $this->getRequest()->getParam('parent_id');

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/menu/edit', ['menu_id' => $menuId]);
        }

        try {
            $menu = $this->menuFactory->create()->load($menuId);
            switch ($entityType) {
                case Item::REMOTE_ENTITY_TYPE_CATEGORY:
                    $menu->importCategory($entityId, $parentId, $mode);
                    break;
            }

            $this->messageManager->addSuccess(__('Import completed.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while importing item.'));
        }

        return $resultRedirect->setPath('*/menu/edit', [
            'item_id' => $parentId ? $parentId : null,
            'menu_id' => $menuId,
        ]);
    }
}
