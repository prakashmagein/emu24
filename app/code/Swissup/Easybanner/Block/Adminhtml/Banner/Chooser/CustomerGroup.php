<?php
namespace Swissup\Easybanner\Block\Adminhtml\Banner\Chooser;

use Magento\Backend\Block\Widget\Grid\Column;

class CustomerGroup extends AbstractChooser
{
    /**
     * @var string
     */
    protected $_routePath;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $_cpCollection;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $_cpCollectionInstance;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_cpCollection = $collectionFactory->create();
        $this->_routePath = '*/banner_widget/chooser';
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define Cooser Grid Columns and filters
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
                'index' => 'customer_group_id',
                'use_index' => true
            ]
        );
        $this->addColumn(
            'customer_group_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'width' => '60px',
                'index' => 'customer_group_id'
            ]
        );
        $this->addColumn(
            'customer_group_code',
            [
                'header' => __('Customer Group'),
                'name' => 'customer_group_code',
                'width' => '80px',
                'index' => 'customer_group_code'
            ]
        );
        return parent::_prepareColumns();
    }
}
