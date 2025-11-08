<?php
namespace Swissup\Reviewreminder\Block\Adminhtml\Index;

/**
 * Admin reminder index
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize reminder edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Swissup_Reviewreminder';
        $this->_controller = 'adminhtml_index';

        parent::_construct();

        if ($this->_isAllowedAction('Swissup_Reviewreminder::save')) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Swissup_Reviewreminder::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete'));
        } else {
            $this->buttonList->remove('delete');
        }

        if ($this->_isAllowedAction('Swissup_Reviewreminder::send')) {
            $this->buttonList->add(
                'send',
                [
                    'class' => 'send',
                    'label' => __('Send'),
                    'onclick' => 'setLocation(\'' . $this->_getSendUrl() . '\')',
                ]
            );
        }
    }
    /**
     * Get url for send reminders
     *
     * @return string
     */
    protected function _getSendUrl()
    {
        return $this->getUrl('reviewreminder/*/send', ['_current' => true]);
    }

    /**
     * Retrieve text for header element
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __("Edit Reminder '%1'", $this->escapeHtml($this->_coreRegistry->registry('reminder')->getCustomerEmail()));
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('reviewreminder/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
}
