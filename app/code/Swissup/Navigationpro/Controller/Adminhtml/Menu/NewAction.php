<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

use Swissup\Navigationpro\Model\MenuFactory;

class NewAction extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_save';

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

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
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->menuFactory = $menuFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * NewAction action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $menu = $this->menuFactory->create();
        $this->registry->register('navigationpro_menu', $menu);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Navigationpro::menu_index')
            ->addBreadcrumb(__('NavigationPro'), __('NavigationPro'))
            ->addBreadcrumb(__('New Menu'), __('New Menu'));

        $resultPage->getConfig()->getTitle()->prepend(__('NavigationPro'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Menu'));
        return $resultPage;
    }
}
