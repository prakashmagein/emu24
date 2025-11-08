<?php
namespace Swissup\Reviewreminder\Controller\Adminhtml\Index;

class Preview extends \Magento\Backend\App\Action
{
    /**
     *
     * @var \Swissup\Reviewreminder\Model\EntityFactory
     */
    protected $reminderFactory;
    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Swissup\Reviewreminder\Helper\Helper
     */
    protected $helper;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory,
        \Swissup\Reviewreminder\Helper\Helper $helper
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->reminderFactory = $reminderFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->reminderFactory->create()->load($id);
        $emailHtml = $this->helper->getEmailPreviewHtml($model);

        return $this->getResponse()->setBody(
            $this->jsonEncoder->encode(['outputHtml' => $emailHtml])
        );
    }
}
