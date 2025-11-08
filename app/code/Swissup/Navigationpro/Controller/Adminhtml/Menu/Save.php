<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Swissup\Navigationpro\Model\MenuFactory;
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
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_save';

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
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('menu_id');

            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = 1;
            }
            if (empty($data['menu_id'])) {
                $data['menu_id'] = null;
            }
            if (empty($data['config_scopes'])) {
                $data['config_scopes'] = [];
            }

            /** @var \Swissup\Navigationpro\Model\Menu $model */
            $menu = $this->menuFactory->create()->load($id);
            if (!$menu->getId() && $id) {
                $this->messageManager->addError(__('This menu no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $menu->addData($data);

            try {
                $menu->save();
                $error = false;
                $message = false;
                $this->dataPersistor->clear('navigationpro_menu');
            } catch (\Exception $e) {
                $error = true;
                $message = $e->getMessage();
            }

            $this->dataPersistor->set('navigationpro_menu', $data);

            if ($this->getRequest()->getParam('isAjax')) {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                    'message' => $message,
                    'error' => $error,
                ]);
            } else {
                if ($message) {
                    $this->messageManager->addError($message);
                }
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $menu->getId()]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
