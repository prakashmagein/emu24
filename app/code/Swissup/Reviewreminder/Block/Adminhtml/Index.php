<?php
namespace Swissup\Reviewreminder\Block\Adminhtml;

/**
 * Adminhtml reminder content block
 */
class Index extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_index';
        $this->_blockGroup = 'Swissup_Reviewreminder';
        $this->_headerText = __('Manage Reminders');

        parent::_construct();
    }
}
