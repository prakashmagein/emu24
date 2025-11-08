<?php
namespace Swissup\Askit\Block\Adminhtml\Question;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * resource model
     *
     * @var \Swissup\Askit\Model\ResourceModel\Question\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Swissup\Askit\Model\ResourceModel\Question\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Swissup\Askit\Model\ResourceModel\Question\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('questionGrid');
        $this->setDefaultSort('created_at');
    }

    /**
     * Prepare collection
     *
     * @inherit
     */
    protected function _prepareCollection()
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Question\Collection|null $collection */
        $collection = $this->getCollection();
        if ($collection === null) {
            $collection = $this->_collectionFactory->create();
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id',
            ]
        );

        $this->addColumn(
            'text',
            [
                'header' => __('Text'),
                'index' => 'text',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'type' => 'select',
                'index' => 'status',
                'renderer' => \Swissup\Askit\Block\Adminhtml\Question\Grid\Renderer\Status::class
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            ]
        );

        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer'),
                'index' => 'customer_name',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            ]
        );

        $this->addColumn(
            'created_time',
            [
                'header' => __('Created'),
                'type' => 'datetime',
                'index' => 'created_time',
            ]
        );

        $this->addColumn(
            'update_time',
            [
                'header' => __('Modified'),
                'type' => 'datetime',
                'index' => 'update_time',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'askit/question/edit'
                        ],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Hide grid mass action elements
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Get row url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('askit/question/edit', ['id' => $row->getId()]);
    }

    /**
     * Determine ajax url for grid refresh
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('askit/question/grid', ['_current' => true]);
    }
}
