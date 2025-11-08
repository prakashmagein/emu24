<?php
namespace Swissup\Reviewreminder\Block\Adminhtml\Index\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Swissup\Reviewreminder\Model\Entity as ReminderModel;

class Products extends Extended
{
    /**
     * Array of review statuses by products
     * @var array
     */
    protected $reviewStatuses = [];
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $setsFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $type;
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $status;
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\Product\LinkFactory $linkFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        array $data = []
    ) {
        $this->setsFactory = $setsFactory;
        $this->productFactory = $productFactory;
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $context->getStoreManager();
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
        $this->reviewFactory = $reviewFactory;
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
        $this->setId('products_grid');
        $this->setDefaultSort('product_id');
        $this->setUseAjax(true);
    }
    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $reminder = $this->coreRegistry->registry('reminder');
        $order = $this->orderFactory->create()->load($reminder->getOrderId());

        $orderWebsiteId = $this->_storeManager->getStore($order->getStoreId())
            ->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($orderWebsiteId);
        $customer->loadByEmail($reminder->getCustomerEmail());
        $customerId = $customer->getId();

        $orderedItems = $order->getAllVisibleItems();
        $orderedProductIds = [];
        foreach ($orderedItems as $item) {
            $productId = $item->getData('product_id');
            if ($customerId) {
                $reviewsCollection = $this->reviewFactory->create()
                    ->getProductCollection();
                $reviewsCollection->addCustomerFilter($customerId);
                $reviewsCollection->addEntityFilter($productId);
                $reviewStatus = $reviewsCollection->load()->getSize() ?
                    ReminderModel::REVIEWED :
                    ReminderModel::NOT_REVIEWED;
            } else {
                $reviewStatus = ReminderModel::NO_CUSTOMER;
            }
            $this->reviewStatuses[$productId] = $reviewStatus;
        }

        foreach ($orderedItems as $item) {
            $productId = $item->getData('product_id');
            array_push($orderedProductIds, $productId);
        }
        $productCollection = $this->productFactory->create()->getCollection()
            ->addIdFilter($orderedProductIds)
            ->addAttributeToSelect(['entity_id', 'name', 'type_id',
                'attribute_set_id', 'status', 'visibility', 'sku', 'price']);

        $this->setCollection($productCollection);
        return parent::_prepareCollection();
    }
    protected function _afterLoadCollection()
    {
        $collection = $this->getCollection();
        $cond = $this->getColumn('review_status')->getFilter()->getCondition();
        foreach ($collection as $product) {
            $product->setReviewStatus($this->reviewStatuses[$product->getId()]);
            if ($cond && $product->getReviewStatus() != $cond['eq'])
            {
                $collection->removeItemByKey($product->getId());
            }
        }
        return $this;
    }
    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->type->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );

        $sets = $this->setsFactory->create()->setEntityTypeFilter(
            $this->productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

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
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        /* @var $model \Swissup\Reviewreminder\Model\Entity */
        $model = $this->coreRegistry->registry('reminder');
        $this->addColumn(
            'review_status',
            [
                'header'    => __('Review Status'),
                'index'     => 'review_status',
                'type'      => 'options',
                'options'   => $model->getReviewStatuses(),
                'filter_condition_callback' => [ $this, 'filterByReviewStatus' ]
            ]
        );

        return parent::_prepareColumns();
    }
    public function filterByReviewStatus($collection, $column)
    {
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
            'reviewreminder/*/products',
            ['_current' => true]
        );
    }
}
