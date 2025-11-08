<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::banner_save';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('banner_id');
        $model = $this->_objectManager->create('Swissup\Easybanner\Model\Banner');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This banner no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('easybanner_banner', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_Easybanner::easybanner')
            ->addBreadcrumb(__('EasyBanner'), __('EasyBanner'))
            ->addBreadcrumb(__('Banners'), __('Banners'))
            ->addBreadcrumb(
                $id ? __('Edit Banner') : __('New Banner'),
                $id ? __('Edit Banner') : __('New Banner')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('EasyBanner'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getIdentifier() : __('New Banner'));

        return $resultPage;
    }
}
