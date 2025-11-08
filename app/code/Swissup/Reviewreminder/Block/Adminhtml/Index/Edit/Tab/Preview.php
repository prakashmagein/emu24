<?php
namespace Swissup\Reviewreminder\Block\Adminhtml\Index\Edit\Tab;

/**
 * Reminder email preview tab
 */
class Preview extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Swissup_Reviewreminder::preview.phtml');
    }
    public function getCurrentEntityId()
    {
        return $this->coreRegistry->registry('reminder')->getEntityId();
    }
}
