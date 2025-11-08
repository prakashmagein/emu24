<?php
namespace Swissup\Attributepages\Controller\Adminhtml\Option;

use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_Attributepages::attributepages_option';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Attributepages::attributepages')
            ->addBreadcrumb(__('Attribute Pages'), __('Attribute Pages'))
            ->addBreadcrumb(__('Manage Options'), __('Manage Options'));
        return $resultPage;
    }
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create('Swissup\Attributepages\Model\Entity');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This option no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $optionId = $this->getRequest()->getParam('option_id');
            $attributeId = $this->getRequest()->getParam('attribute_id');
            if (!$optionId || !$attributeId) {
                $this->messageManager->addError(__('Invalid link received.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->setOptionId($optionId);
            $model->setAttributeId($attributeId);
        }

        $data = $this->dataPersistor->get('attributepages_option');
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('attributepages_page', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(__('Edit Option'), __('Edit Option'));
        $resultPage->getConfig()->getTitle()->prepend(__('Attribute Pages'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : $model->getOption()->getValue());

        return $resultPage;
    }
}
