<?php
namespace Swissup\Attributepages\Controller\Adminhtml\Page;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }
    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Swissup_Attributepages::attributepages'
        )->_addBreadcrumb(
            __('Attribute Pages'),
            __('Attribute Pages')
        )->_addBreadcrumb(
            __('Manage Pages'),
            __('Manage Pages')
        );
        return $this;
    }
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('attribute_id')) {
            $this->_forward('edit');
            return;
        }
        $model = $this->_objectManager->create('Swissup\Attributepages\Model\Entity');
        $this->coreRegistry->register('attributepages_page', $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Page'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Attribute Pages'));
        $this->_view->renderLayout();
    }
}
