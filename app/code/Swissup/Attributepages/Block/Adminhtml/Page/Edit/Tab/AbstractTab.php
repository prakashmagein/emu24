<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab;

class AbstractTab extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Get page model
     * @return Swissup\Attributepages\Model\Entity
     */
    public function getPage()
    {
        return $this->_coreRegistry->registry('attributepages_page');
    }
    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return (bool)$this->getPage()->getAttributeId();
    }
    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        $prefix = 'Swissup_Attributepages::page_';
        if ($this->getPage()->getOption()) {
            $prefix = 'Swissup_Attributepages::option_';
        }
        return $this->_authorization->isAllowed($prefix . $action);
    }
}
