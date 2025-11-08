<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Swissup\Navigationpro\Model\MenuFactory;
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
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_delete';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        MenuFactory $menuFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->menuFactory = $menuFactory;
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

        $id = $this->getRequest()->getParam('menu_id');
        if ($id) {
            try {
                $model = $this->menuFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the menu.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a menu to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
