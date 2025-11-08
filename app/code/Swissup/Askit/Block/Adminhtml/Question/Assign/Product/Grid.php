<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Assign\Product;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

use Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity\Context;
use Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity\Grid as AbstractGrid;

class Grid extends AbstractGrid
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param Context           $context
     * @param CollectionFactory $productCollectionFactory
     * @param array             $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('swissup_askit_question_assigned_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_question') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getQuestion()->getId()) {
            $this->setDefaultFilter(['in_question' => 1]);
        }
        $collection = $this->productCollectionFactory->create()->addAttributeToSelect(['name', 'sku', 'price']);
        $this->setCollection($collection);

        if ($this->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getProductsReadonly()) {
            $this->addColumn(
                'in_question',
                [
                    'type' => 'checkbox',
                    'name' => 'in_question',
                    'values' => $this->_getSelectedProducts(),
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );

        return parent::_prepareColumns();
    }

    public function getProductsReadonly()
    {
        return false;
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('askit/assign/productsGrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $products = $request->getPost('assign_products', null);
        if (!is_array($products)) {
            $products = $this->getAssignProducts();
        }
        return $products;
    }

    /**
     * @return array
     */
    public function getAssignProducts()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $products = $request->getPost('assign_products', null);
        if ($products === null) {
            $products = $this->getQuestion()->getAssignProducts();
            return $products;
        }
        return $products;
    }
}
