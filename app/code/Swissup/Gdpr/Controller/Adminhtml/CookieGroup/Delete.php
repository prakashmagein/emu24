<?php

namespace Swissup\Gdpr\Controller\Adminhtml\CookieGroup;

use Magento\Backend\App\Action\Context;
use Swissup\Gdpr\Model\CookieGroupFactory;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @var CookieGroupFactory
     */
    protected $groupFactory;

    /**
     * @param Context $context
     * @param CookieGroupFactory $groupFactory
     */
    public function __construct(
        Context $context,
        CookieGroupFactory $groupFactory
    ) {
        $this->groupFactory = $groupFactory;
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

        $id = $this->getRequest()->getParam('group_id');
        if ($id) {
            try {
                $model = $this->groupFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                return $resultRedirect->setPath('*/cookie/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }

        $this->messageManager->addError(__('We can\'t find an item to delete.'));

        return $resultRedirect->setPath('*/cookie/');
    }
}
