<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Swissup\Gdpr\Model\CookieFactory;
use Swissup\Gdpr\Model\ResourceModel\Cookie\BuiltInCollectionFactory;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @var CookieFactory
     */
    protected $cookieFactory;

    /**
     * @var BuiltInCollectionFactory
     */
    protected $builtInCollectionFactory;

    /**
     * @param Context $context
     * @param CookieFactory $cookieFactory
     */
    public function __construct(
        Context $context,
        CookieFactory $cookieFactory,
        BuiltInCollectionFactory $builtInCollectionFactory
    ) {
        parent::__construct($context);
        $this->cookieFactory = $cookieFactory;
        $this->builtInCollectionFactory = $builtInCollectionFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('cookie_id');
        $name = $this->getRequest()->getParam('name');
        $cookie = $this->cookieFactory->create();
        $title = $name ?: __('New Cookie');

        if ($id) {
            $cookie->load($id);
            if (!$cookie->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect  */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $title = $cookie->getName();
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Swissup_Gdpr::cookieregistry')
            ->addBreadcrumb(__('Cookie Registry'), __('Cookie Registry'))
            ->addBreadcrumb($title, $title);

        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Registry'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        if (!$id) {
            $resultPage->getConfig()->addBodyClass('gdpr-storeview-hidden');
        }

        return $resultPage;
    }
}
