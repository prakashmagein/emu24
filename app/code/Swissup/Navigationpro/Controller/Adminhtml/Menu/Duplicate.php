<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action\Context;
use Swissup\Navigationpro\Model\Menu\Copier;

class Duplicate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_save';

    private Copier $copier;

    public function __construct(Context $context, Copier $copier)
    {
        $this->copier = $copier;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('menu_id');

        if (!$id) {
            $this->messageManager->addError(__('Unable to find menu to duplicate.'));
            return $this->_redirect('*/menu/edit', ['_current' => true]);
        }

        try {
            $model = $this->copier->copy($id);
            $this->messageManager->addSuccess(__('Menu has been duplicated.'));
            return $this->_redirect('*/menu/edit', ['_current' => true, 'menu_id' => $model->getMenuId()]);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/menu/edit', ['_current' => true]);
        }
    }
}
