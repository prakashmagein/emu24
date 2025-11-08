<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Assign\Categories;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

use Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity\Context;
use Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity\Grid as AbstractGrid;

class Grid extends AbstractGrid
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context           $context
     * @param CollectionFactory $collectionFactory
     * @param array             $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('swissup_askit_question_assigned_categories');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
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
            $categoriesIds = $this->_getSelectedCategories();
            if (empty($categoriesIds)) {
                $categoriesIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $categoriesIds]);
            } elseif (!empty($categoriesIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $categoriesIds]);
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
        $messageId = (int) $this->getRequest()->getParam('id', 0);
        if ($this->getQuestion()->getId()) {
            $this->setDefaultFilter(['in_question' => 1]);
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->addAttributeToSelect('*')
            ->joinUrlRewrite();

        $this->setCollection($collection);

        if ($this->getCategoriesReadonly()) {
            $categoriesIds = $this->_getSelectedCategories();
            if (empty($categoriesIds)) {
                $categoriesIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $categoriesIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getCategoriesReadonly()) {
            $this->addColumn(
                'in_question',
                [
                    'type' => 'checkbox',
                    'name' => 'in_question',
                    'values' => $this->_getSelectedCategories(),
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

        $this->addColumn('request_path', ['header' => __('Path'), 'index' => 'request_path']);

        return parent::_prepareColumns();
    }

    public function getCategoriesReadonly()
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
        return $this->getUrl('askit/assign/categoriesGrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedCategories()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $assigns = $request->getPost('assign_categories', null);
        if (!is_array($assigns)) {
            $assigns = $this->getAssignCategories();
        }
        return $assigns;
    }

    /**
     * @return array
     */
    public function getAssignCategories()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $assigns = $request->getPost('assign_categories', null);
        if ($assigns === null) {
            $assigns = $this->getQuestion()->getAssignCategories();
            return $assigns;
        }
        return $assigns;
    }

    /**
     * Get children of specified item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array|null
     */
    public function getMultipleRows($item)
    {
        // Fix because collection have a column children
        return null;
        // return $item->getChildren();
    }
}
