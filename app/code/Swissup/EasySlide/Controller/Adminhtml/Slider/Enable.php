<?php

namespace Swissup\EasySlide\Controller\Adminhtml\Slider;

class Enable extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_EasySlide::save';

    /**
     * @var string
     */
    protected $msgSuccess = 'Slider "%1" was enabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 1;

    /**
     * @var \Swissup\EasySlide\Model\SliderFactory
     */
    protected $sliderFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\EasySlide\Model\SliderFactory $sliderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\EasySlide\Model\SliderFactory $sliderFactory
    ) {
        $this->sliderFactory = $sliderFactory;
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

        $id = $this->getRequest()->getParam('slider_id');
        if ($id) {
            try {
                $model = $this->sliderFactory->create();
                $model->load($id);
                $model->setIsActive($this->newStatusCode);
                $model->save();
                $this->messageManager->addSuccess(__($this->msgSuccess, $model->getIdentifier()));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['slider_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
