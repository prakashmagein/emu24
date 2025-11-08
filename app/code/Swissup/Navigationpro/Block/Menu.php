<?php

namespace Swissup\Navigationpro\Block;

use Swissup\Navigationpro\Model\Menu\Source\ContentDisplayMode;
use Swissup\Navigationpro\Data\TreeFactory;
use Swissup\Navigationpro\Data\Tree\Node;
use Swissup\Navigationpro\Data\Tree\NodeFactory;
use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

class Menu extends Template implements IdentityInterface
{
    /**
     * Cache identities
     *
     * @var array
     */
    protected $identities = [];

    /**
     * @var \Swissup\Navigationpro\Model\Menu
     */
    protected $menu;

    /**
     * @var \Swissup\Navigationpro\Model\MenuFactory
     */
    protected $menuFactory;

    /**
     * Data tree node.
     *
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * Data tree.
     *
     * @var TreeFactory
     */
    protected $treeFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Swissup\Navigationpro\Model\Resolver\DataProvider\Menu
     */
    private $dataProvider;

    /**
     * @var bool
     */
    private $isDataProviderPrepared = false;

    /**
     * @var string
     */
    private $output;

    /**
     * @param Template\Context $context
     * @param \Swissup\Navigationpro\Model\Resolver\DataProvider\MenuFactory $dataProviderFactory
     * @param CustomerSession $customerSession
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Navigationpro\Model\Resolver\DataProvider\MenuFactory $dataProviderFactory,
        CustomerSession $customerSession,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->dataProvider = $dataProviderFactory->create();
        $this->customerSession = $customerSession;
        $this->layerResolver = $layerResolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        if ($this->isSlideoutEnabledAndVisible()) {
            $this->pageConfig->addBodyClass('navpro-with-slideout');
        }

        if ($this->_scopeConfig->getValue('navigationpro/top/identifier', ScopeInterface::SCOPE_STORE)) {
            $this->getLayout()->unsetElement('catalog.topnav');
        }

        return parent::_prepareLayout();
    }

    private function prepareDataProvider()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $widgetParameters = new \Magento\Framework\DataObject();
        $widgetParameterKeys = [
            'identifier', 'show_active_branch', 'show_parent', 'visible_levels', 'theme',
            'orientation', 'dropdown_side', 'css_class', 'wrap', 'block_title', 'block_css',
            'nav_css_class',
        ];
        foreach($widgetParameterKeys as $widgetParameterKey) {
            $widgetParameters->setData($widgetParameterKey, $this->getData($widgetParameterKey));
        }
        $this->dataProvider
            ->setMenu($this->getMenu())
            ->setStoreId($storeId)
            ->setCurrentCategory($this->getCurrentCategory())
            ->setCategoryId($this->getCategoryId())
            ->setWidgetParameters($widgetParameters);
    }

    /**
     * @return \Swissup\Navigationpro\Model\Resolver\DataProvider\Menu
     */
    private function getDataProvider()
    {
        if ($this->isDataProviderPrepared === false) {
            $this->prepareDataProvider();
            $this->isDataProviderPrepared = true;
        }

        return $this->dataProvider;
    }

    /**
     * @return array
     */
    private function getJsWidgetConfig()
    {
        $config = $this->getDataProvider()->getTree()->getDropdownPositionConfig();

        $maxWidth = $this->getVar('breakpoints/mobile/conditions/max-width', 'Magento_Catalog');
        if ($maxWidth) {
            $config['mediaBreakpoint'] = "(max-width: {$maxWidth})";
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        if ($this->jsLayout) {
            return parent::getJsLayout();
        }

        $this->jsLayout = [
            'navpro' => $this->getJsWidgetConfig()
        ];

        if ($this->isStickyEnabled()) {
            $this->jsLayout['Swissup_Navigationpro/js/sticky'] = [];
        }

        return parent::getJsLayout();
    }

    /**
     * Get CSS classes for the NAV element
     *
     * @return string
     */
    public function getNavCssClass()
    {
        $classes = $this->getDataProvider()->getTree()->getNavCssClasses();

        return implode(' ', $classes);
    }

    /**
     * Get css classes for the UL element
     *
     * @return string
     */
    public function getCssClass($all = false)
    {
        $dataProvider = $this->getDataProvider();

        $classes = $all ? $dataProvider->getAllUlCssClasses() : $dataProvider->getUlCssClasses();

        return implode(' ', $classes);
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = parent::getTemplate();

        if ($template) {
            return $template;
        }

        return 'Swissup_Navigationpro::menu.phtml';
    }

    /**
     * Get menu html
     *
     * @return string
     */
    public function getHtml($outermostClass = '')
    {
        if (!$this->getMenu()) {
            return '';
        }

        if ($this->output === null) {

            $html = $this->getDataProvider()
                ->setOutermostClass($outermostClass)
                ->render();

            $this->output = $html;
        }

        return $this->output;
    }

    /**
     * @return \Swissup\Navigationpro\Model\Menu
     */
    public function getMenu()
    {
        $id = $this->getIdentifier();
        if (!isset($this->menu) && $id) {
            $this->menu = $this->dataProvider
                ->setIdentifier($id)
                ->getMenu();
        }
        return $this->menu;
    }

    /**
     * Add identity
     *
     * @param string|array $identity
     * @return void
     */
    public function addIdentity($identity)
    {
        if (!in_array($identity, $this->identities)) {
            $this->identities[] = $identity;
        }
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array_merge(
            [
                \Swissup\Navigationpro\Model\Menu::CACHE_TAG . '_' . $this->getMenu()->getId(),
            ],
            $this->identities
        );
    }

    /**
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        if ($this->getData('cache_lifetime') === false) {
            return null;
        }

        return parent::getCacheLifetime() ?: 3600;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        // $keyInfo[] = $this->getUrl('*/*/*', ['_current' => true, '_query' => '']);

        $activeCategory = $this->getCurrentCategory();
        if ($activeCategory) {
            $keyInfo[] = Category::CACHE_TAG . '_' . $activeCategory->getId();
        }

        // customer group and currency
        $keyInfo[] = $this->customerSession->getCustomerGroupId();
        $keyInfo[] = $this->_storeManager->getStore()->getCurrentCurrencyCode();

        // widget data
        $keys = [
            'identifier',
            'show_active_branch',
            'visible_levels',
            'theme',
            'orientation',
            'dropdown_side',
            'css_class',
            'nav_css_class',
        ];
        foreach ($keys as $key) {
            if (!$this->hasData($key)) {
                continue;
            }
            $keyInfo[$key] = $key . ':' . $this->getData($key);
        }

        return $keyInfo;
    }

    /**
     * Get current Category from catalog layer
     *
     * @return \Magento\Catalog\Model\Category
     */
    private function getCurrentCategory()
    {
        $catalogLayer = $this->layerResolver->get();

        if (!$catalogLayer) {
            return null;
        }

        return $catalogLayer->getCurrentCategory();
    }

    /**
     * @return boolean
     */
    private function isStickyEnabled()
    {
        $isSticky = strpos($this->getCssClass(), 'navpro-sticky') !== false;
        $isSlideOut = strpos($this->getNavCssClass(), 'navpro-slideout') !== false;

        return $isSticky && !$isSlideOut;
    }

    /**
     * @return boolean
     */
    private function isSlideoutEnabledAndVisible()
    {
        $isSlideOut = strpos($this->getNavCssClass(), 'navpro-slideout') !== false;
        $isVisible = strpos($this->getCssClass(), 'navpro-slideout-silent') === false;

        return $isSlideOut && $isVisible;
    }
}
