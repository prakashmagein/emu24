<?php
namespace Swissup\Easybanner\Block\Adminhtml\Banner\Chooser;

use Magento\Backend\Block\Widget\Grid\Column;

class Handle extends AbstractChooser
{
    /**
     * @var string
     */
    protected $_routePath;

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Handle\Collection
     */
    protected $_cpCollection;

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Handle\Collection
     */
    protected $_cpCollectionInstance;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavAttSetCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $cpCollection
     * @param \Magento\Catalog\Model\Product\Type $catalogType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Swissup\Easybanner\Model\ResourceModel\Handle\Collection $cpCollection,
        array $data = []
    ) {
        $this->_cpCollection = $cpCollection;
        $this->_routePath = '*/banner_widget/chooser';
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define Chooser Grid Columns and filters
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'id',
                'use_index' => true
            ]
        );

        $this->addColumn(
            'id',
            [
                'header' => __('Handles'),
                'sortable' => true,
                'width' => '60px',
                'index' => 'id'
            ]
        );

        return parent::_prepareColumns();
    }
}
