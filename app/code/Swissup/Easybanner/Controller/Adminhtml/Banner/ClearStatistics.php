<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;

class ClearStatistics extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::banner_delete';

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\BannerStatisticFactory
     */
    private $statisticsFactory;

    /**
     * @param Context $context
     * @param \Swissup\Easybanner\Model\ResourceModel\BannerStatisticFactory $statisticsFactory
     */
    public function __construct(
        Context $context,
        \Swissup\Easybanner\Model\ResourceModel\BannerStatisticFactory $statisticsFactory
    ) {
        parent::__construct($context);

        $this->statisticsFactory = $statisticsFactory;
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
                $id = $id === 'all' ? null : $id;

                $statistics = $this->statisticsFactory->create();
                $rows = $statistics->clear($id);

                $this->messageManager->addSuccess(__(
                    'Banner statistics was cleared. %1 rows were removed.',
                    $rows
                ));

                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'banner_id' => $id,
                        '_current' => true
                    ]);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', ['banner_id' => $id]);
                }
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
