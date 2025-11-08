<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Assign\Pages;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

use Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity\Context;
use Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity\Grid as AbstractGrid;

class Grid extends AbstractGrid
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * @param Context                 $context
     * @param CollectionFactory       $collectionFactory
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param array                   $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Cms\Model\Page $cmsPage,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->cmsPage = $cmsPage;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('swissup_askit_question_assigned_pages');
        $this->setDefaultSort('page_id');
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
            $pageIds = $this->_getSelectedPages();
            if (empty($pageIds)) {
                $pageIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('page_id', ['in' => $pageIds]);
            } elseif (!empty($pageIds)) {
                $this->getCollection()->addFieldToFilter('page_id', ['nin' => $pageIds]);
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

        /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection*/
        $collection = $this->collectionFactory->create();

        $this->setCollection($collection);

        if ($this->getPagesReadonly()) {
            $pageIds = $this->_getSelectedPages();
            if (empty($pageIds)) {
                $pageIds = 0;
            }
            $this->getCollection()->addFieldToFilter('page_id', ['in' => $pageIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getPagesReadonly()) {
            $this->addColumn(
                'in_question',
                [
                    'type' => 'checkbox',
                    'name' => 'in_question',
                    'values' => $this->_getSelectedPages(),
                    'index' => 'page_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }
        $this->addColumn(
            'page_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'page_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);

        $this->addColumn('identifier', ['header' => __('URL Key'), 'index' => 'identifier']);

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => $this->cmsPage->getAvailableStatuses()
            ]
        );

        return parent::_prepareColumns();
    }

    public function getPagesReadonly()
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
        return $this->getUrl('askit/assign/pagesGrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedPages()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $pages = $request->getPost('assign_pages', null);
        if (!is_array($pages)) {
            $pages = $this->getAssignPages();
        }
        return $pages;
    }

    /**
     * @return array
     */
    public function getAssignPages()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $pages = $request->getPost('assign_pages', null);
        if ($pages === null) {
            $pages = $this->getQuestion()->getAssignPages();
            return $pages;
        }
        return $pages;
    }
}
