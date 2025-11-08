<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

class Enable extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::banner_save';

    /**
     * @var string
     */
    protected $msgSuccess = 'Banner "%1" was enabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 1;

    /**
     * @var \Swissup\Easybanner\Model\PlaceholderFactory
     */
    protected $bannerFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\Easybanner\Model\BannerFactory $bannerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Easybanner\Model\BannerFactory $bannerFactory
    ){
        $this->bannerFactory = $bannerFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('banner_id');
        if ($id) {
            try {
                $model = $this->bannerFactory->create();
                $model->load($id);
                $model->setStatus($this->newStatusCode);
                $model->save();
                $this->messageManager->addSuccess(__($this->msgSuccess, $model->getIdentifier()));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['banner_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
