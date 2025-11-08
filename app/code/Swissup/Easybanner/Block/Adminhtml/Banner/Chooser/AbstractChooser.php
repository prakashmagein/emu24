<?php
namespace Swissup\Easybanner\Block\Adminhtml\Banner\Chooser;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;

class AbstractChooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_routePath;

    protected $_cpCollection;

    protected $_cpCollectionInstance;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $reflestion = new \ReflectionClass($this);
            $this->setId($reflestion->getShortName() . 'Grid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }


    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $selected = $this->_getSelectedProducts();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter(
                    $column->getIndex(),
                    ['in' => $selected]
                );
            } else {
                $this->getCollection()->addFieldToFilter(
                    $column->getIndex(),
                    ['nin' => $selected]
                );
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected', []);
        return $products;
    }

    /**
     * Prepare Collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_getCpCollectionInstance();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Get resource collection instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getCpCollectionInstance()
    {
        if (!$this->_cpCollectionInstance) {
            $this->_cpCollectionInstance = $this->_cpCollection;
        }
        return $this->_cpCollectionInstance;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            $this->_routePath,
            ['_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null]
        );
    }
}
