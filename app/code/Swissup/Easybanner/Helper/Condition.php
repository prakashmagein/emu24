<?php

namespace Swissup\Easybanner\Helper;

class Condition extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Layout
     */
    private $layout;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $datetime;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    private $productCondition;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    private $productConditionFactory;

    /**
     * @var array
     */
    private $productAttributes;

    /**
     * @param \Magento\Framework\App\Helper\Context                    $context
     * @param \Magento\Framework\Registry                              $registry
     * @param \Magento\Framework\View\Layout                           $layout
     * @param \Magento\Framework\Stdlib\DateTime\DateTime              $datetime
     * @param \Magento\Customer\Model\Session                          $customerSession
     * @param \Magento\Checkout\Model\Session                          $checkoutSession
     * @param \Magento\Tax\Helper\Data                                 $taxHelper
     * @param \Magento\Catalog\Model\ProductRepository                 $productRepository
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $productConditionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $productConditionFactory
    ) {
        $this->registry = $registry;
        $this->layout = $layout;
        $this->datetime = $datetime;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->taxHelper = $taxHelper;
        $this->productRepository = $productRepository;
        $this->productConditionFactory = $productConditionFactory;

        parent::__construct($context);
    }

    /**
     * Retrieve currently viewing product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getCurrentProduct()
    {
        if ($product = $this->registry->registry('product')) {
            return $product;
        }

        if (!$productId = $this->getRequestParam('product_id')) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            $product = false;
        }

        return $product;
    }

    /**
     * Get attributes that could be used in condition rules
     *
     * @return array
     */
    public function getProductAttributes()
    {
        if ($this->productAttributes !== null) {
            return $this->productAttributes;
        }

        if ($this->productCondition === null) {
            $this->productCondition = $this->productConditionFactory->create();
        }

        $this->productAttributes = $this->productCondition
            ->loadAttributeOptions()
            ->getAttributeOption();

        return $this->productAttributes;
    }

    /**
     * Get condition value
     *
     * @param  string                           $attribute
     * @param  \Swissup\Easybanner\Model\Banner $model
     * @return mixed
     */
    public function getValue($attribute, \Swissup\Easybanner\Model\Banner $model)
    {
        switch ($attribute) {
            case 'category_ids':
                if ($category = $this->registry->registry('current_category')) {
                    $comparator = $category->getId();
                } else {
                    $comparator = $this->getRequestParam('category_id');
                }
                break;
            case 'product_ids':
                if ($product = $this->registry->registry('product')) {
                    $comparator = $product->getId();
                } else {
                    $comparator = $this->getRequestParam('product_id');
                }
                break;
            case 'time':
                $comparator = strtotime($this->datetime->gmtDate('H:i'));
                break;
            case 'date':
                $comparator = strtotime($this->datetime->gmtDate('Y-m-d H:i:s'));
                break;
            case 'monthday':
                $comparator = $this->datetime->gmtDate('j');
                break;
            case 'weekday':
                $comparator = $this->datetime->gmtDate('w');
                break;
            case 'handle':
                $comparator = $this->layout->getUpdate()->getHandles();
                break;
            case 'url':
                $comparator = $this->_urlBuilder->getCurrentUrl();
                break;
            case 'clicks_count':
                if (!$model->getIsTrackable()) {
                    return true;
                }

                $comparator = $model->getClicksCount();
                break;
            case 'display_count':
                if (!$model->getIsTrackable()) {
                    return true;
                }

                $comparator = $model->getDisplayCount();
                break;
            case 'customer_group':
                $comparator = $this->customerSession->getCustomerGroupId();
                break;
            case 'subtotal_excl':
                $comparator = $this->getSubtotal();
                break;
            case 'subtotal_incl':
                $comparator = $this->getSubtotal(true);
                break;
            default:
                // client side filters
                // filters always has only 1 element, so we can return here
                return true;
        }

        return $comparator;
    }

    /**
     * Get cart subtotal
     *
     * @param  boolean $withTax
     * @return float|false
     */
    private function getSubtotal($withTax = false)
    {
        $totals = $this->checkoutSession->getQuote()->getTotals();

        if (isset($totals['subtotal'])) {
            $config = $this->taxHelper->getConfig();
            if ($config->displayCartSubtotalBoth()) {
                if ($withTax) {
                    $subtotal = $totals['subtotal']->getValueInclTax();
                } else {
                    $subtotal = $totals['subtotal']->getValueExclTax();
                }
            } elseif ($config->displayCartSubtotalInclTax()) {
                $subtotal = $totals['subtotal']->getValueInclTax();
            } else {
                $subtotal = $totals['subtotal']->getValue();
                if ($withTax && isset($totals['tax'])) {
                    $subtotal += $totals['tax']->getValue();
                }
            }

            return $subtotal;
        }

        return false;
    }

    /**
     * Get parameter from request object
     *
     * @param  string $param
     * @return string
     */
    private function getRequestParam($param)
    {
        $value = null;
        $request = $this->_getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        switch ($param) {
            case 'category_id':
                if ('catalog' === $module && 'view' === $action) {
                    if ('category' === $controller) {
                        $value = $request->getParam('id');
                    } elseif ('product' === $controller) {
                        $value = $request->getParam('category');
                    }
                }
                break;
            case 'product_id':
                if ('catalog' === $module
                    && 'product' === $controller
                    && 'view' === $action) {

                    $value = $request->getParam('id');
                }
                break;
        }

        return $value;
    }
}
