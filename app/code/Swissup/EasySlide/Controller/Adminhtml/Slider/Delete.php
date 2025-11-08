<?php

namespace Swissup\EasySlide\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Swissup\EasySlide\Model\SliderFactory;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_EasySlide::delete';

    /**
     * @var SliderFactory
     */
    protected $sliderFactory;

    /**
     * @param Context $context
     * @param SliderFactory $sliderFactory
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory
    ) {
        $this->sliderFactory = $sliderFactory;
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

        $id = $this->getRequest()->getParam('slider_id');
        if ($id) {
            try {
                $model = $this->sliderFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['slider_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find an item to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
