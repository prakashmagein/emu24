<?php

namespace Swissup\Easybanner\Block\Adminhtml\Banner\Widget;

use Swissup\Easybanner\Block\Adminhtml\Widget\AbstractChooser;

class Chooser extends AbstractChooser
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Swissup\Easybanner\Model\BannerFactory $bannerFactory,
        \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_entityFactory = $bannerFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_routePath = 'easybanner/banner/chooser';
        $this->_entityLabelField = 'identifier';
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
                'index' => 'banner_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'chooser_identifier',
            [
                'header' => __('Identifier'),
                'index' => 'identifier',
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
