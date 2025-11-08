<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab;

class Options extends \Magento\Backend\Block\Widget\Grid\Extended
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    protected $entityCollectionFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $entityCollectionFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $entityCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->entityCollectionFactory = $entityCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('excluded_option_grid');
        $this->setDefaultSort('value');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setVarNamePage('p');
        if ($this->getPage()->getId()) {
            $this->setDefaultFilter(['show_excluded_options' => '0']);
        }
    }
    /**
     * Get page model
     * @return Swissup\Attributepages\Model\Entity
     */
    public function getPage()
    {
        return $this->coreRegistry->registry('attributepages_page');
    }
    /**
     * Add filter
     *
     * @param object $column
     * @return Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Options
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'show_excluded_options') {
            $optionIds = $this->_getExcludedOptions();
            if (empty($optionIds)) {
                $optionIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.option_id', ['in' => $optionIds]);
            } else {
                if ($optionIds) {
                    $this->getCollection()->addFieldToFilter('main_table.option_id', ['nin' => $optionIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->getPage()->getRelatedOptions();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $options  = $this->getCollection();
        $entities = $this->entityCollectionFactory->create()
            ->addOptionOnlyFilter()
            ->addFieldToFilter('option_id', ['in' => $options->getColumnValues('option_id')])
            ->addStoreFilter($this->_storeManager->getStore())
            ->load();
        foreach ($options as $option) {
            $entity = $entities->getItemByColumnValue('option_id', $option->getOptionId());
            if ($entity) {
                $option->addData($entity->getData());
            } else {
                $identifier = $option->getValue();
                if (function_exists('mb_strtolower')) {
                    $identifier = mb_strtolower($identifier, 'UTF-8');
                }
                $option->setIdentifier($identifier);
            }
        }
        return parent::_afterLoadCollection();
    }
    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('show_excluded_options', [
            'header_css_class' => 'a-center',
            'header' => __('Exclude from Display'),
            'type'   => 'checkbox',
            'name'   => 'show_excluded_options',
            'values' => $this->_getExcludedOptions(),
            'align'  => 'center',
            'index'  => 'option_id'
        ]);
        $this->addColumn('option_id', [
            'header'   => __('ID'),
            'sortable' => true,
            'width'    => 60,
            'index'    => 'option_id'
        ]);
        $this->addColumn('value', [
            'header' => __('Name'),
            'index'  => 'value'
        ]);
        $this->addColumn('identifier', [
            'header'   => __('URL Key'),
            'width'    => 200,
            'sortable' => false,
            'filter'   => false,
            'renderer' => 'Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer\Identifier'
        ]);
        $this->addColumn('image', [
            'header' => __('Image'),
            'width'     => 220,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer\Image'
        ]);
        $this->addColumn('thumbnail', [
            'header' => __('Thumbnail'),
            'width'     => 220,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer\Thumbnail'
        ]);
        $this->addColumn('sort_order', [
            'header'   => __('Sort Order'),
            'sortable' => false,
            'filter'   => false,
            'renderer' => \Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer\SortOrder::class
        ]);
        return parent::_prepareColumns();
    }
    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            '*/*/optionsGrid',
            ['_current' => true]
        );
    }
    /**
     * Retrieve excluded options
     *
     * @return array
     */
    protected function _getExcludedOptions()
    {
        $options = $this->getOptionsExcluded();
        if (!is_array($options)) {
            $options = $this->getExcludedOptions();
        }
        return $options;
    }
    /**
     * Retrieve excluded options
     *
     * @return array
     */
    public function getExcludedOptions()
    {
        return $this->getPage()->getExcludedOptionIdsArray();
    }

    public function getTabLabel()
    {
        return __('Options');
    }
    public function getTabTitle()
    {
        return __('Options');
    }
    public function canShowTab()
    {
        return (bool)$this->getPage()->getAttributeId();
    }
    public function isHidden()
    {
        return !(bool)$this->getPage()->getAttributeId();
    }
}
