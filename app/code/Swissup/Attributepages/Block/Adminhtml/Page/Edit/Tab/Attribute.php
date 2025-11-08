<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab;

class Attribute extends AbstractTab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory
     */
    protected $attrCollectionFactory;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory $attrCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        array $data = []
    ) {
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Attribute');
    }
    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Attribute');
    }
    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return !(bool)$this->getPage()->getAttributeId();
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Select Attribute')]);

        $this->_addElementTypes($fieldset);

        // get the attributes with options available only
        $attributes = $this->attrCollectionFactory->create()
            ->setFrontendInputTypeFilter(['select', 'multiselect'])
            ->addOrder('frontend_label', 'ASC')
            ->load();
        $oldIds = $attributes->getAllIds();
        $options = $this->attrOptionCollectionFactory->create()
            ->addFieldToFilter('attribute_id', ['in' => $oldIds]);
        $options->getSelect()->group('attribute_id');
        $newIds = $options->getColumnValues('attribute_id');
        $idsToRemove = array_diff($oldIds, $newIds);
        foreach ($idsToRemove as $idToRemove) {
            $attributes->removeItemByKey($idToRemove);
        }
        // end of attributes retrieving

        if (!$attributes->count()) {
            $fieldset->addField('req_text', 'note', [
                'text' => __('Please create an attribute (Stores > Attribute > Product) to use as a source for attribute-based page. Make sure to create attribute with one of the following "Catalog Input Types": Multiple Select, Dropdown, Visual Swatch, Text Swatch.')
            ]);
        } else {
            $fieldset->addField(
                'attribute_id',
                'select',
                [
                    'name' => 'attribute_id',
                    'label' => __('Attribute'),
                    'title' => __('Attribute'),
                    'required' => true,
                    'values' => $attributes->toOptionArray()
                ]
            );

            $continueButton = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Continue'),
                    'onclick' => "setAttributeToUse('" . $this->getContinueUrl() . "', 'attribute_id')",
                    'class' => 'save'
                ]
            );
            $fieldset->addField('continue_button', 'note', ['text' => $continueButton->toHtml()]);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
    /**
     * Return url for continue button
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl(
            '*/*/new',
            [
                '_current' => true,
                'attribute_id' => '<%- data.attribute_id %>',
                '_escape_params' => false
            ]
        );
    }
}
