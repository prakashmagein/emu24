<?php

namespace Swissup\Gdpr\Controller\Adminhtml\CookieGroup;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;
use Swissup\Gdpr\Model\CookieGroupFactory;
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
     * @var CookieGroupFactory
     */
    protected $groupFactory;

    /**
     * @param Context $context
     * @param CookieGroupFactory $groupFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        CookieGroupFactory $groupFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->groupFactory = $groupFactory;
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

        $id = $this->getRequest()->getParam('group_id');
        $storeId = $this->getRequest()->getParam('store_id', Store::DEFAULT_STORE_ID);
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
        }

        if (isset($data['required']) && $data['required'] === 'true') {
            $data['required'] = 1;
        }
        if (empty($data['group_id'])) {
            $data['group_id'] = null;
        }

        $group = $this->groupFactory->create();
        if ($id) {
            $group->setStoreId($storeId)->load($id);
        }

        if (!$group->getId() && $id) {
            $this->messageManager->addError(__('This item no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $group->addData($this->processUseDefault($data));

        try {
            $group->save();
            $id = $group->getId();
            $this->dataPersistor->clear('gdpr_cookiegroup');
            $this->messageManager->addSuccess(__('You saved item.'));
        } catch (\Exception $e) {
            $this->dataPersistor->set('gdpr_cookiegroup', $data);
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/edit', [
            'group_id' => $id,
            'store' => $storeId,
        ]);
    }
}
