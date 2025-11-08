<?php

namespace Swissup\Easybanner\Block\Adminhtml\Placeholder\Widget;

use Swissup\Easybanner\Block\Adminhtml\Widget\AbstractChooser;

class Chooser extends AbstractChooser
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Swissup\Easybanner\Model\PlaceholderFactory $placeholderFactory
     * @param \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Swissup\Easybanner\Model\PlaceholderFactory $placeholderFactory,
        \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_entityFactory = $placeholderFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_routePath = 'easybanner/placeholder/chooser';
        $this->_entityLabelField = 'name';
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Prepare columns for pages grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'chooser_id',
            [
                'header' => __('ID'),
                'index' => 'placeholder_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'chooser_name',
            [
                'header' => __('Placeholder'),
                'index' => 'name',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        $this->addColumn(
            'chooser_status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [
                    '0' => __('Disabled'),
                    '1' => __('Enabled')
                ],
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        return parent::_prepareColumns();
    }
}
