<?php

namespace Swissup\SoldTogether\Block\Adminhtml\AssignedProducts;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\CatalogRule\Model\Rule\Action\SimpleActionOptionsProvider as RulesProvider;

class Grid extends GridExtended
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     * @param Visibility|null $visibility
     * @param Status|null $status
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = [],
        ?Visibility $visibility = null,
        ?Status $status = null
    ) {
        $this->_productFactory = $productFactory;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->status = $status ?: ObjectManager::getInstance()->get(Status::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('st_weight');
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for is_linked flag
        if ($column->getId() == 'is_linked') {
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
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $additionalFields = ['st_weight' => 'weight'];
        if ($this->getLinkType() == 'order') {
            $additionalFields[] = 'data_serialized';
        }

        $this->setDefaultFilter(['is_linked' => 1]);
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'visibility'
        )->addAttributeToSelect(
            'status'
        )->joinTable(
            ['st' => "swissup_soldtogether_{$this->getLinkType()}"],
            'related_id=entity_id',
            $additionalFields,
            'product_id=' . (int)$this->getCurrentProductId(),
            'left'
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'is_linked',
            [
                'type' => 'checkbox',
                'name' => 'is_linked',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
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
        $this->addColumn(
            'thumbnail',
            [
                'header' => __('Thumbnail'),
                'index' => 'thumbnail',
                'type' => 'thumbnail',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );
        $this->getColumnSet()
            ->getChildBlock('thumbnail')
            ->setRendererType('thumbnail', Grid\Column\Renderer::class);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );

        if ($this->getLinkType() == 'order') {
            $this->addColumn(
                'promo_rule',
                [
                    'header' => __('Promo Rule'),
                    'type' => 'select',
                    'options' => $this->getRules(),
                    'index' => 'promo_rule',
                    'validate_class' => 'class="admin__control-select"',
                    'editable' => true
                ]
            );
            $this->addColumn(
                'promo_value',
                [
                    'header' => __('Promo Value'),
                    'index' => 'promo_value',
                    'validate_class' => 'admin__control-text',
                    'editable' => true
                ]
            );
        }

        $this->addColumn(
            'st_weight',
            [
                'header' => __('Weight'),
                'index' => 'st_weight',
                'editable' => true
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {inheritdoc}
     */
    public function getCollection()
    {
        $collection = parent::getCollection();
        if ($collection && $collection->isLoaded()) {
            $collection->walk(function ($item) {
                if ($item->hasDataSerialized()) {
                    $item->addData(json_decode($item->getDataSerialized(), true) ?: []);
                    $item->unsDataSerialized();
                }
            });
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('soldtogether/product/assignedGrid', [
            'link_type' => $this->getLinkType(),
            'product' => $this->getCurrentProductId()
        ]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            return $this->getData('selected_products');
        }

        return $products;
    }

    private function getRules(): array
    {
        $rulesProvider = ObjectManager::getInstance()->get(RulesProvider::class);
        $options = array_merge(
            [
                [
                    'value' => '',
                    'label' => __('Do nothing')
                ]
            ],
            $rulesProvider->toOptionArray()
        );

        $rules = [];
        foreach ($options as $option) {
            $rules[$option['value'] ?? ''] = $option['label'] ?? '';
        }

        return $rules;
    }
}
