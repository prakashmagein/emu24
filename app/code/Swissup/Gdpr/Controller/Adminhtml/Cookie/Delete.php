<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Backend\App\Action\Context;
use Swissup\Gdpr\Model\CookieFactory;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @var CookieFactory
     */
    protected $cookieFactory;

    /**
     * @param Context $context
     * @param CookieFactory $cookieFactory
     */
    public function __construct(
        Context $context,
        CookieFactory $cookieFactory
    ) {
        $this->cookieFactory = $cookieFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('cookie_id');

        if ($id) {
            try {
                $model = $this->cookieFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['cookie_id' => $id]);
            }
        }

        $this->messageManager->addError(__('We can\'t find an item to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
