<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

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
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Swissup_Attributepages';
        $this->_controller = 'adminhtml_page';

        parent::_construct();

        if (!$this->getPage()->getAttributeId()) {
            $this->removeButton('save');
            $this->removeButton('delete');
            $this->removeButton('reset');
            return;
        }

        if ($this->_isAllowedAction('save')) {
            $this->buttonList->update('save', 'label', __('Save Page'));

            $this->buttonList->add(
                'save_and_edit_button',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                100
            );

            if ($this->getPage()->getId()) {
                $this->addButton('duplicate', [
                    'label'   => __('Duplicate'),
                    'onclick' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                    'class'   => 'add'
                ]);
            }
        } else {
            $this->removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Page'));
        } else {
            $this->removeButton('delete');
        }
    }

    public function getPage()
    {
        return $this->coreRegistry->registry('attributepages_page');
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
    /**
     * Return translated header text depending on creating/editing action
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getPage()->getId()) {
            return __("Edit Page '%1'", $this->escapeHtml($this->getPage()->getName()));
        } elseif ($option = $this->getPage()->getOption()) {
            return $option->getValue();
        } else {
            return __('New Page');
        }
    }
    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', ['_current' => true]);
    }
    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => null]);
    }
}
