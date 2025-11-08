<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;

use Swissup\Navigationpro\Model\MenuFactory;
use Swissup\Navigationpro\Model\ItemFactory;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_edit';

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        MenuFactory $menuFactory,
        ItemFactory $itemFactory,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->menuFactory = $menuFactory;
        $this->itemFactory = $itemFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $menu = $this->menuFactory->create();
        $menu->load($this->getRequest()->getParam('menu_id'));
        $this->registry->register('navigationpro_menu', $menu);

        $storeId = $this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        $item = $this->itemFactory->create()
            ->setStoreId($storeId)
            ->setMenuId($menu->getId());

        $itemId = $this->getRequest()->getParam('item_id', null);
        if ($itemId !== null) {
            $item->load($itemId);

            if (!$item->getId()) {
                if ($itemId) {
                    $this->messageManager->addError(__('This item no longer exists.'));
                }
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/edit', [
                    'menu_id' => $menu->getId()
                ]);
            }
        }

        $item->setExists((int)(boolean)$item->getId());
        $this->registry->register('navigationpro_item', $item);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Navigationpro::menu_index')
            ->addBreadcrumb(__('NavigationPro'), __('NavigationPro'))
            ->addBreadcrumb(
                $menu->getIdentifier() . ($item->getId() ? ': ' . $item->getName() : ''),
                $menu->getIdentifier() . ($item->getId() ? ': ' . $item->getName() : '')
            );

        $resultPage->getConfig()->getTitle()->prepend(__('NavigationPro'));
        $resultPage->getConfig()->getTitle()->prepend(
            $menu->getIdentifier() . ($item->getId() ? ': ' . $item->getName() : '')
        );

        return $resultPage;
    }
}
