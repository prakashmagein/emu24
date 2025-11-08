<?php
namespace Swissup\Amp\Helper;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Toolbar sort order modifiers
     * @var array
     */
    protected $orderModifiers = [
        '*' => [
            'asc'   => 'Asc',
            'desc'  => 'Desc'
        ],
        'relevance' => [
            'asc'   => false,
            'desc'  => ''
        ],
        'position' => [
            'asc'   => '',
            'desc'  => false
        ],
        'price' => [
            'asc'   => 'Low - High',
            'desc'  => 'High - Low'
        ],
        'name' => [
            'asc'   => 'A - Z',
            'desc'  => 'Z - A'
        ]
    ];

    /**
     * Array of available review templates
     *
     * @var array
     */
    protected $reviewTemplates = [
        ReviewRendererInterface::FULL_VIEW => 'Swissup_Amp::review/helper/summary.phtml',
        ReviewRendererInterface::SHORT_VIEW => 'Swissup_Amp::review/helper/summary_short.phtml',
    ];

    /**
     * @var ReviewRendererInterface
     */
    protected $reviewRenderer;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ReviewRendererInterface $reviewRenderer
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Swissup\Amp\Helper\Data $configHelper
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ReviewRendererInterface $reviewRenderer,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Swissup\Amp\Helper\Data $configHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->reviewRenderer = $reviewRenderer;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->layout = $layout;
        $this->formKey = $formKey;
        $this->configHelper = $configHelper;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Get sort order modifiers
     *
     * @return array
     */
    public function getOrderModifiers()
    {
        return $this->orderModifiers;
    }

    /**
     * Retrieve Pager URL
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param string $order
     * @param string $direction
     * @return string
     */
    public function getOrderUrl($toolbar, $order, $direction)
    {
        if (is_null($order)) {
            $order = $toolbar->getCurrentOrder() ?: $toolbar->getAvailableOrders()[0];
        }

        return $toolbar->getPagerUrl([
            ToolbarModel::ORDER_PARAM_NAME => $order,
            ToolbarModel::DIRECTION_PARAM_NAME => $direction,
            $toolbar->getPageVarName() => null
        ]);
    }

    /**
     * Check if layered navigation block will be rendered
     *
     * @return boolean
     */
    public function canShowShopBy()
    {
        $block = $this->getLayeredNavigationBlock();
        if (!$block) {
            return false;
        }

        if (method_exists($block, 'canShowBlock') && !$block->canShowBlock()) {
            // Magento\LayeredNavigation\Block\Navigation
            return false;
        } elseif (method_exists($block, 'getCurrentChildCategories')) {
            // Magento\Catalog\Block\Navigation
            $categories = $block->getCurrentChildCategories();
            $count = is_array($categories) ? count($categories) : $categories->count();
            if (!$count) {
                return false;
            }
        }

        if ($this->configHelper->disableLayeredNavigation()) {
            return false;
        }

        return true;
    }

    /**
     * Get layered navigation active filters
     *
     * @return array
     */
    public function getActiveFilters()
    {
        $block = $this->getLayeredNavigationBlock();
        if (!$block) {
            return [];
        }

        if (!$block->getLayer() || !$block->getLayer()->getState()) {
            return [];
        }

        return $block->getLayer()->getState()->getFilters();
    }

    /**
     * Get layered navigation block
     *
     * @return \Magento\LayeredNavigation\Block\Navigation
     */
    public function getLayeredNavigationBlock()
    {
        $block = $this->layout->getBlock('catalog.leftnav');
        if (!$block) {
            $block = $this->layout->getBlock('catalogsearch.leftnav');
        }

        return $block;
    }

    /**
     * Get review summary html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = ReviewRendererInterface::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if ($product->getRatingSummary() === null) {
            if (class_exists(\Magento\Review\Model\ReviewSummary::class)) {
                // magento 2.3.3 and newer
                \Magento\Framework\App\ObjectManager::getInstance()
                    ->create(\Magento\Review\Model\ReviewSummary::class)
                    ->appendSummaryDataToObject(
                        $product,
                        $this->storeManager->getStore()->getId()
                    );
            } else {
                // magento 2.3.2 and older
                $this->reviewFactory->create()->getEntitySummary(
                    $product,
                    $this->storeManager->getStore()->getId()
                );
            }
        }

        if (null === $product->getRatingSummary() && !$displayIfNoReviews) {
            return '';
        }

        if (empty($this->reviewTemplates[$templateType])) {
            $templateType = ReviewRendererInterface::DEFAULT_VIEW;
        }
        $this->reviewRenderer->setTemplate($this->reviewTemplates[$templateType]);
        $this->reviewRenderer->setDisplayIfEmpty($displayIfNoReviews);
        $this->reviewRenderer->setProduct($product);

        return $this->reviewRenderer->toHtml();
    }

    /**
     * Returns array of css classes to render star icons
     *
     * @param  int $summary Rating summary in percents
     * @return array
     */
    public function getSummaryIcons($summary)
    {
        $result = [];
        $stars = $summary / 20;
        $stars = round($stars * 2) / 2;

        $i = 1;
        do {
            if ($i <= $stars) {
                $result[] = 'icon-star';
            } elseif ($i < ($stars + 1)) {
                $result[] = 'icon-star-half';
            } else {
                $result[] = 'icon-star-outline';
            }
        } while ($i++ < 5);

        return $result;
    }

    /**
     * Detect if 'Add to Cart' can be used
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function canShowAddToCart($product)
    {
        // Files are not supported by AMP yet
        $unsupportedOptionTypes = ['file'];
        if ($product->getOptions()) {
            foreach ($product->getOptions() as $option) {
                if (in_array($option->getType(), $unsupportedOptionTypes)) {
                    return false;
                }
            }
        }

        if (!$this->configHelper->addToCartFullModeEnabled()) {
            $supportedTypes = $this->configHelper->getSelectedProductTypes();
            $supportedTypes = explode(',', $supportedTypes);
            if (!in_array($product->getTypeId(), $supportedTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate url with json params and form_key
     * @param  string $route
     * @param  string $json
     * @return string
     */
    public function getUrlFromJson($route, $json)
    {
        $params = json_decode($json, true);
        $params = $params['data'];
        $params['form_key'] = $this->formKey->getFormKey();

        return $this->_getUrl($route, $params);
    }

    /**
     * Added for M2.1 compatibility
     * @param  string $str
     * @return string
     */
    public function escapeHtmlAttr($str)
    {
        return $this->escaper->escapeHtmlAttr($str);
    }
}
