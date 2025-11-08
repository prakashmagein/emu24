<?php
namespace Swissup\Attributepages\Controller\Adminhtml\Page;

class Options extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->coreRegistry = $registry;
    }
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Swissup\Attributepages\Model\Entity');
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        } elseif ($attributeId = $this->getRequest()->getParam('attribute_id')) {
            $model->setAttributeId($attributeId);
        }
        $this->coreRegistry->register('attributepages_page', $model);

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('attributepages_page_edit_tab_options')
            ->setOptionsExcluded($this->getRequest()->getPost('options_excluded', null));
        return $resultLayout;
    }
}
