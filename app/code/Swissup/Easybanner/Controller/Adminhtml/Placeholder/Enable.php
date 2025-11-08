<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

class Enable extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::placeholder_save';

    /**
     * @var string
     */
    protected $msgSuccess = 'Placeholder "%1" was enabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 1;

    /**
     * @var \Swissup\Easybanner\Model\PlaceholderFactory
     */
    protected $placeholderFactory = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\Easybanner\Model\PlaceholderFactory $placeholderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Easybanner\Model\PlaceholderFactory $placeholderFactory
    ) {
        $this->placeholderFactory = $placeholderFactory;
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

        $id = $this->getRequest()->getParam('placeholder_id');
        if ($id) {
            try {
                $model = $this->placeholderFactory->create();
                $model->load($id);
                $model->setStatus($this->newStatusCode);
                $model->save();
                $this->messageManager->addSuccess(
                    __($this->msgSuccess, $model->getName())
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['placeholder_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
