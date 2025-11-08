<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

class Duplicate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::banner_save';

    /**
     * @var \Swissup\Easybanner\Model\BannerFactory
     */
    private $bannerFactory;

    /**
     * @var \Swissup\Easybanner\Model\Banner\Copier
     */
    private $bannerCopier;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Easybanner\Model\BannerFactory $bannerFactory,
        \Swissup\Easybanner\Model\Banner\Copier $bannerCopier
    ) {
        parent::__construct($context);

        $this->bannerFactory = $bannerFactory;
        $this->bannerCopier = $bannerCopier;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('banner_id');
        if (!$id) {
            $this->_redirect('*/*/');
        }

        try {
            $model = $this->bannerFactory->create();
            $model->load($id);

            if (!$model->getId()) {
                return $this->_redirect('*/*/');
            }

            $newModel = $this->bannerCopier->copy($model);

            $this->messageManager->addSuccess(__('The banner has been duplicated.'));
            return $this->_redirect('*/*/edit', [
                '_current' => true,
                'banner_id' => $newModel->getId(),
            ]);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/*/edit', ['banner_id' => $id]);
        }
    }
}
