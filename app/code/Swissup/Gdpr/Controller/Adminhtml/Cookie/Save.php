<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;
use Swissup\Gdpr\Model\CookieFactory;
use Swissup\Gdpr\Controller\Adminhtml\Traits\WithUseDefault;

class Save extends \Magento\Backend\App\Action
{
    use WithUseDefault;

    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var CookieFactory
     */
    protected $cookieFactory;

    /**
     * @param Context $context
     * @param CookieFactory $cookieFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        CookieFactory $cookieFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->cookieFactory = $cookieFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('cookie_id');
        $storeId = $this->getRequest()->getParam('store_id', Store::DEFAULT_STORE_ID);
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/edit', ['cookie_id' => $id]);
        }

        if (isset($data['status']) && $data['status'] === 'true') {
            $data['status'] = 1;
        }
        if (empty($data['cookie_id'])) {
            $data['cookie_id'] = null;
        }

        $cookie = $this->cookieFactory->create();
        if ($id) {
            $cookie->setStoreId($storeId)->load($id);
        }

        if (!$cookie->getId() && $id) {
            $this->messageManager->addError(__('This item no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $cookie->addData($this->processUseDefault($data));

        try {
            $cookie->save();
            $id = $cookie->getId();
            $this->dataPersistor->clear('gdpr_cookie');
            $this->messageManager->addSuccess(__('You saved item.'));
        } catch (\Exception $e) {
            $this->dataPersistor->set('gdpr_cookie', $data);
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/edit', [
            'cookie_id' => $id,
            'store' => $storeId,
        ]);
    }
}
