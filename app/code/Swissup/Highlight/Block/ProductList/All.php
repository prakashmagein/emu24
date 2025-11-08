<?php

namespace Swissup\Highlight\Block\ProductList;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Swissup\Highlight\Model\Config\Source\PaginationType;

class All extends \Magento\Catalog\Block\Product\ListProduct implements \Magento\Widget\Block\BlockInterface
{
    private $rendererListBlock;
    const PAGE_TYPE = null;

    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = \Swissup\Highlight\Block\ProductList\Toolbar::class;

    /**
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $widgetPager;

    /**
     * @var string
     */
    protected $widgetPageVarName = 'hap';

    /**
     * @var \Swissup\Highlight\Block\ProductList\Toolbar
     */
    protected $toolbar;

    /**
     * @var string
     */
    protected $widgetPriceSuffix = 'all';

    /**
     * @var string
     */
    protected $widgetCssClass = 'highlight-all';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Swissup\Highlight\Helper\Page
     */
    protected $pageHelper;

    /**
     * @var \Swissup\Highlight\Model\Resolver\DataProvider\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var AppState
     */
    private $appState;

    private $scopeConfig;

    /**
     * @var Swissup\Highlight\Block\Wrapper
     */
    protected $wrapper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Swissup\Highlight\Helper\Page $highlightHelper,
        \Swissup\Highlight\Model\Resolver\DataProvider\ConditionsFactory $conditionsFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
        $this->pageHelper = $highlightHelper;
        $this->conditions = $conditionsFactory->create();
        $this->priceCurrency = $priceCurrency;
        $this->layoutFactory = $layoutFactory;
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;

        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create($this->getProductCollectionType());

            $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
            $collection = $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter()
                ->setPageSize($this->getPageSize())
                ->setCurPage($this->getCurrentPage());

            $toolbar = $this->getToolbar();
            if ($toolbar) {
                /* @var $toolbar \Magento\Catalog\Block\Product\ProductList\Toolbar */
                $toolbar = $this->configureToolbar($toolbar);

                $toolbar = $this->initToolbar($toolbar, $collection); //@ todo move logic into Toolbar::setCollection
                // set collection to toolbar and apply sort
                $toolbar->setCollection($collection);
            }

            // Don't use _catalogLayer because it filters the products by current category
            // which doesn't works good on the homepage when `use_category_in_url` option
            // is used and products are not linked to the site's root category.
            // @see Magento\Catalog\Model\Layer\Category\CollectionFilter::filter
            //  `->addUrlRewrite($category->getId())`
            //
            // The main downside of this change - is product urls will not include
            // parent category. Just like Magento's widgets.
            //
            // We will revert this change and add note to the docs about adding products
            // to the root category in case of some serious degradation.
            //
            // $this->_catalogLayer->prepareProductCollection($collection);
            // $collection->addStoreFilter();

            $this->prepareProductCollection($collection);

            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );

            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * @return string
     */
    public function getProductCollectionType()
    {
        return \Swissup\Highlight\Model\ResourceModel\Product\CollectionFactory::TYPE_DEFAULT;
    }

    /**
     * Use this method to apply manual filters, etc
     *
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function prepareProductCollection($collection)
    {
        \Magento\Framework\Profiler::start(__METHOD__);

        $this->getConditions()->attachToCollection($collection);

        \Magento\Framework\Profiler::stop(__METHOD__);
    }

    protected function getConditions()
    {
        if (!$this->conditions->getData('conditions_encoded')) {
            $conditions = $this->getData('conditions')
                ? $this->getData('conditions')
                : $this->getData('conditions_encoded');

            $this->conditions->setConditions($conditions);

            if ($ids = $this->getData('category_ids')) {
                // ajax pagination
                $this->conditions->setCategoryIds($ids);
            } else {
                // first page
                $this->setData('category_ids', $this->conditions->getCategoryIds());
            }
        }

        return $this->conditions;
    }

    /**
     * Returns currently viewed, comma separated category ids, if
     * 'current' condition is used. Otherwise returns empty string.
     *
     * @return string
     */
    public function getCategoryIds()
    {
        return $this->getConditions()->getCategoryIds();
    }

    /**
     * @return array
     */
    public function getConditionsDecoded()
    {
        return $this->getConditions()->getConditionsDecoded();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];

        foreach ($this->_getProductCollection() as $item) {
            $identities[] = $item->getIdentities();
        }

        return array_unique(array_merge([], ...$identities));
    }

    protected function _prepareLayout()
    {
        $result = parent::_prepareLayout();

        // add missing addto container with compare and wishlist links
        $addto = $this->addChild(
            'addto',
            \Magento\Catalog\Block\Product\ProductList\Item\Container::class
        );
        $addto->addChild(
            'wishlist',
            \Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist::class,
            [
                'template' => 'Magento_Wishlist::catalog/product/list/addto/wishlist.phtml',
            ]
        );
        $addto->addChild(
            'compare',
            \Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare::class,
            [
                'template' => 'Magento_Catalog::product/list/addto/compare.phtml',
            ]
        );

        return $result;
    }


    /**********************************************************
    ******************** Widget Specific Methods **************
    **********************************************************/
    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(
            [
                'cache_lifetime' => 86400,
                'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]
            ]
        );
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if (false === $this->getIsWidget()) {
            return parent::getCacheKeyInfo();
        }

        $conditions = $this->getData('conditions')
            ? $this->getData('conditions')
            : $this->getData('conditions_encoded');

        return [
            'HIGHLIGHT',
            $this->priceCurrency->getCurrency()->getCode(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'disable_wrapper' => $this->getDisableWrapper(),
            $this->getWrapper()->getTemplate(),
            $this->getAttributeCode(),
            $this->getCarousel(),
            $this->getMode(),
            $this->getOrder(),
            $this->getDir(),
            $this->getCurrentPage(),
            $this->getProductsCount(),
            $this->getLimit(),
            $this->showPager(),
            $this->getPriceSuffix(),
            $this->getCssClass(),
            $this->getPageVarName(),
            $this->getPaginationType(),
            $conditions,
            $this->getCategoryIds(),
            $this->getTitle(),
            $this->getShowPageLink(),
            $this->getPageLinkPosition(),
            $this->getPageUrl(),
            $this->getPageLinkTitle(),
        ];
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if ($this->getHideWhenFilterIsUsed()) {
            if ($this->_request->getParam('p', 1) > 1) {
                return '';
            }

            if (count($this->_catalogLayer->getState()->getFilters())) {
                return '';
            }
        }

        if (empty($this->_template)) {
            $this->_template = $this->getCustomTemplate();
        } else {
            // deprecated templates
            $deprecatedTemplates = [
                'product/widget/content/grid.phtml',
                'Swissup_Highlight::product/widget/content/grid.phtml',
                'product/widget/content/list.phtml',
                'Swissup_Highlight::product/widget/content/list.phtml',
                'product/widget/content/grid-carousel.phtml',
                'Swissup_Highlight::product/widget/content/grid-carousel.phtml',
            ];
            if (in_array($this->_template, $deprecatedTemplates)) {
                $this->setData('mode', 'grid');

                if (strpos($this->_template, 'list.phtml') !== false) {
                    $this->setData('mode', 'list');
                } elseif (strpos($this->_template, 'grid-carousel.phtml') !== false) {
                    $this->setData('carousel', true);
                }

                $this->_template = 'Swissup_Highlight::product/list.phtml';
            }
        }

        return $this->_template;
    }

    /**
     * @return void
     */
    protected function _beforeToHtml()
    {
        // prevent one more toolbar initialization (Private methods)
        return $this;
    }

    /**
     * Add toolbar block from product listing layout
     *
     */
    private function getToolbar()
    {
        $toolbar = $this->getToolbarFromLayout();

        if (!$toolbar) {
            $toolbar = $this->getToolbarBlock();
            $this->setToolbarBlockName($toolbar->getNameInLayout());
        }

        return $toolbar;
    }

    /**
     * Get toolbar block from layout
     *
     * @return bool|Toolbar
     */
    private function getToolbarFromLayout()
    {
        $blockName = $this->getToolbarBlockName();

        $toolbar = false;

        if ($blockName) {
            $toolbar = $this->getLayout()->getBlock($blockName);
        }

        return $toolbar;
    }

    /**
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function getToolbarBlock()
    {
        if ($this->toolbar) {
            return $this->toolbar;
        }

        // Check for toolbar block in layout
        if ($this->toolbar = $this->getToolbarFromLayout()) {
            return $this->toolbar;
        }

        $this->toolbar = $this->getLayout()->createBlock(
            $this->_defaultToolbarBlock,
            $this->getToolbarBlockName()
        );

        return $this->toolbar;
    }

    /**
     * @param $toolbar
     * @param $collection
     * @return
     */
    private function configureToolbar($toolbar)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        $toolbar->setPaginationType($this->getPaginationType());
        $maxPageCount = $this->getPageCount();
        if ($maxPageCount) {
            $toolbar->setMaxPageCount($maxPageCount);
        }

        $this->setChild('toolbar', $toolbar);

        $orders = array_keys($toolbar->getAvailableOrders());
        $defaultOrder = $this->getDefaultSortField();
        if (!in_array($defaultOrder, $orders)) {
            $toolbar->addOrderToAvailableOrders($this->getDefaultSortField(), $this->getDefaultSortFieldLabel());
            $toolbar->setDefaultOrder($this->getDefaultSortField());
            $toolbar->setDefaultDirection($this->getDefaultSortDirection());
        }

        if ($this->getIsWidget() === false) {
            if ($this->getOrder()) {
                $toolbar->setDefaultOrder($this->getOrder());
            }
            if ($this->getDir()) {
                $toolbar->setDefaultDirection($this->getDir());
            }
        } else {
            // $toolbar->setData('_current_grid_mode', $this->getMode());
            $toolbar->setHidePagination(!$this->showPager());
            $toolbar->setHideLimiter(true);
            $toolbar->setData('_current_limit', $this->getProductsCount());
            $toolbar->setData('_current_page', $this->getCurrentPage());
            $toolbar->setData('_current_grid_direction', $this->getDir());
            if ($this->hasOrder() && $this->getOrder() !== 'default') {
                $toolbar->setData('_current_grid_order', $this->getOrder());
            }
        }

//        $this->setChild('toolbar', $toolbar);

        return $toolbar;
    }

    /**
     * Use this method to apply manual sort order, etc
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected function initToolbar($toolbar, $collection)
    {
        if (false !== $this->getIsWidget()) {

            // additional sort order parameter, use it to sort by attribute
            if ($this->hasOrder() && $this->getOrder() !== 'default') {
                $order = $this->getOrder();
                $toolbar->setSkipOrder(true);
                if (in_array(strtolower($order), ['rand()', 'rand', 'random'])) {
                    $collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));
                } else {
                    $collection->setOrder($order, $this->getDir());
                }
            }
        }

        // sort by column, alias, etc
        if ($this->getRawOrder()) {
            $toolbar->setSkipOrder(true);
            $toolbar->setRawOrder($this->getRawOrder());
        }

        return $toolbar;
    }

    /**
     * @return string
     */
    public function getDefaultSortField()
    {
        return 'position';
    }

    /**
     * @return string
     */
    public function getDefaultSortDirection()
    {
        return 'ASC';
    }

    /**
     * @return array|mixed|string|null
     */
    public function getDir()
    {
        if (!$this->hasData('dir')) {
            return $this->getDefaultSortDirection();
        }
        return $this->getData('dir');
    }

    /**
     * Get number of current page based on query value
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $param = $this->getRequest()->getParam($this->getPageVarName());
        return $param ? abs((int)$param) : 1;
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            return 5;
        }
        return $this->getData('products_count');
    }

    /**
     * Retrieve how many products should be displayed in pagination
     *
     * @return int
     */
    protected function getLimit()
    {
        $pageCount = $this->getPageCount();
        if (!$pageCount) {
            $pageCount = 1;
        }
        return $pageCount * $this->getProductsCount();
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if ($this->getCarousel()) {
            return false;
        }

        return $this->getIsShowPager() !== false && $this->getPageCount() > 1;
    }

    /**
     * Fast method to check if we have more product to show.
     */
    public function hasMorePages(): bool
    {
        $this->_getProductCollection();
        return $this->getToolbar()->hasMorePages();
    }

    public function getMorePagesCount(): int
    {
        $this->_getProductCollection();
        return $this->getToolbar()->getMorePagesCount();
    }

    /**
     * @return array|mixed|null
     */
    public function getPriceSuffix()
    {
        if (!$this->hasData('price_suffix')) {
            $this->setData('price_suffix', $this->widgetPriceSuffix);
        }
        return $this->getData('price_suffix');
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        $cssClasses = [
            'block-highlight',
            $this->getCarousel() ? 'highlight-carousel' : '',
            $this->getMode() ? 'highlight-' . $this->getMode() : '',
            $this->getMode() === 'grid' ? 'highlight-cols-' . $this->getColumnCount() : '',
            $this->widgetCssClass,
            $this->getData('css_class'),
        ];

        return implode(' ', array_filter($cssClasses));
    }

    /**
     * @return array|mixed|null
     */
    public function getPageVarName()
    {
        if (!$this->hasData('page_var_name')) {
            $this->setData('page_var_name', $this->widgetPageVarName);
        }
        return $this->getData('page_var_name');
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        if (false === $this->getIsWidget()) {
            return parent::getMode();
        }
        return $this->getData('mode') ? $this->getData('mode') : 'grid';
    }

    /**
     * @return false|string
     */
    public function getPageUrl()
    {
        if ($this->hasData('page_url')) {
            return $this->pageHelper->getDirectUrl($this->getData('page_url'));
        }

        if (!static::PAGE_TYPE) {
            return false;
        }
        return $this->pageHelper->getPageUrl(static::PAGE_TYPE);
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->showPager() && $this->getPagerBlock()) {
            return $this->getPagerBlock()->toHtml();
        }
        return '';
    }

    /**
     * @return \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    public function getPagerBlock()
    {
        if (!$this->widgetPager) {
            $this->widgetPager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                $this->getToolbarBlock()->getNameInLayout() . '_pager'
            );

            if ($this->getPaginationType() === PaginationType::TYPE_IMPROVED) {
                $this->widgetPager
                    ->setTemplate('Swissup_Highlight::product/list/toolbar-improved-pager.phtml')
                    ->setFramePagesOptimized(range(
                        max(1, $this->getCurrentPage() - 3),
                        $this->getCurrentPage() + $this->getMorePagesCount()
                    ))
                    ->setHasMorePages($this->hasMorePages());
            }

            $this->widgetPager->setUseContainer(true)
                ->setShowAmounts(true)
                ->setShowPerPage(false)
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getProductsCount())
                ->setTotalLimit($this->getLimit())
                ->setCollection($this->getProductCollection());
        }
        return $this->widgetPager;
    }

    public function getPaginationType()
    {
        if (!$this->hasData('pagination_type')) {
            $type = $this->scopeConfig->getValue(
                'highlight/general/pagination_type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if (!$type || !in_array($type, [PaginationType::TYPE_DEFAULT, PaginationType::TYPE_IMPROVED])) {
                $type = PaginationType::TYPE_DEFAULT;
            }

            $this->setData('pagination_type', $type);
        }

        return $this->getData('pagination_type');
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        if (false === $this->getIsWidget()) {
            return parent::getToolbarHtml();
        }
        return '';
    }

    /**
     * Render block HTML and wrap it into highlight markup, if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }

        $html = parent::_toHtml();
        if (!$html
            || !$this->getProductCollection()
            || !$this->getProductCollection()->count()
        ) {
            return '';
        }

        if ($this->getDisableWrapper()) {
            return $html;
        }

        return $this->getWrapper()->setContent($html)->toHtml();
    }

    protected function getWrapper()
    {
        if (!$this->wrapper) {
            $this->wrapper = $this->getLayout()
                ->createBlock(\Swissup\Highlight\Block\Wrapper::class)
                ->setHighlightBlock($this);
        }

        return $this->wrapper;
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone'])
            ? $arguments['zone']
            : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * @return Render
     */
    protected function getPriceRender()
    {
        $block = $this->getLayout()->getBlock('product.price.render.default');
        if ($block) {
            $block->setData('is_product_list', true);
        }
        return $block;
    }

    /**
     * This code is taken from \Magento\CatalogWidget\Block\Product\ProductList
     */
    protected function getDetailsRendererList()
    {
        $frontendArea = \Magento\Framework\App\Area::AREA_FRONTEND;
        try {
            $areaCode = $this->appState->getAreaCode();
            $isAreaCodeEmulated = $this->appState->isAreaCodeEmulated();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }
        if (($isAreaCodeEmulated && $areaCode === $frontendArea)
            || !in_array($areaCode, [$frontendArea], true)
        ) {
            return;
        }

        if (empty($this->rendererListBlock)) {
            /** @var $layout \Magento\Framework\View\LayoutInterface */
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }
        return $this->rendererListBlock;
    }

    /**
     * @return array|mixed|string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitleImageUrl()
    {
        // get title image from block data
        if ($imageUrl = $this->getData('title_image_url')) {
            if (false !== strpos($imageUrl, '://')) {
                return $imageUrl;
            }

            return $this->_storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . $imageUrl;
        }

        return '';
    }
}
