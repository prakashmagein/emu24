<?php

namespace Swissup\Ajaxsearch\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryFactory;
use Swissup\Ajaxsearch\Model\Config\Source\Design\FormLayout;

class Data extends \Magento\Search\Helper\Data
{
    /**
     * @var string
     */
    const CONFIG_XML_PATH_ENABLE = 'ajaxsearch/main/enable';

    /**
     * @var string
     */
    const CONFIG_XML_PATH_GRAPHQL = 'ajaxsearch/main/graphql';

    /**
     * @var string
     */
    const CONFIG_PATH_FORM_LAYOUT = 'ajaxsearch/folded/enable';

    /**
     * @var string
     */
    const CONFIG_PATH_AUTOCOMPLETE_ENABLE = 'ajaxsearch/autocomplete/enable';

    /**
     * @var string
     */
    const CONFIG_PATH_AUTOCOMPLETE_ON_FOCUS   = 'ajaxsearch/autocomplete/show_on_focus';

    /**
     * @var string
     */
    const CONFIG_PATH_AUTOCOMPLETE_LIMIT  = 'ajaxsearch/autocomplete/limit';

    /**
     * @var string
     */
    const CONFIG_PATH_PRODUCT_ENABLE = 'ajaxsearch/product/enable';

    /**
     * @var string
     */
    const CONFIG_PATH_PRODUCT_LIMIT  = 'ajaxsearch/product/limit';

    /**
     * @var string
     */
    const CONFIG_PATH_PRODUCT_VIEW_ALL  = 'ajaxsearch/product/viewall';

    /**
     * @var string
     */
    const CONFIG_PATH_CATEGORY_ENABLE = 'ajaxsearch/category/enable';

    /**
     * @var string
     */
    const CONFIG_PATH_CATEGORY_FILTER_ENABLE = 'ajaxsearch/category/filter';

    /**
     * @var string
     */
    const CONFIG_PATH_CATEGORY_LIMIT  = 'ajaxsearch/category/limit';

    /**
     * @var string
     */
    const CONFIG_PATH_CATEGORY_DEPTH  = 'ajaxsearch/category/depth';

    /**
     * @var string
     */
    const CONFIG_PATH_CATEGORY_GRAPHQL  = 'ajaxsearch/category/graphql';

    /**
     * @var string
     */
    const CONFIG_PATH_PAGE_ENABLE = 'ajaxsearch/page/enable';

    /**
     * @var string
     */
    const CONFIG_PATH_PAGE_LIMIT  = 'ajaxsearch/page/limit';

    // const PLACEHOLDER = 'ajaxsearch/main/placeholder';
    /**
     * @var string
     */
    const CONFIG_PATH_LIMIT       = 'ajaxsearch/main/limit';

    /**
     * @var string
     */
    const CONFIG_PATH_HIGHLIGHT   = 'ajaxsearch/main/highlight';

    /**
     * @var string
     */
    const CONFIG_PATH_HINT        = 'ajaxsearch/main/hint';

    /**
     * @var string
     */
    const CONFIG_PATH_CLASSNAMES  = 'ajaxsearch/main/classNames';

    /**
     * @var string
     */
    const CONFIG_PATH_RESULTS_LAYOUT = 'ajaxsearch/design/layout';

    /**
     * @var string
     */
    const CONFIG_PATH_TAX_DISPLAY_TYPE = 'tax/display/type';

    /**
     *
     * @param  string $key
     * @param mixed $store
     * @return mixed
     */
    private function getConfig($key, $store = null)
    {
        return $this->scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param  int  $store
     * @param  string $key
     * @return boolean
     */
    private function isSetFlag($key, $store = null)
    {
        return $this->scopeConfig->isSetFlag($key, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     *
     * @param  int  $store
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_ENABLE, $store);
    }

    /**
     *
     * @return int
     */
    public function getLimit()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_LIMIT);
    }

    // /**
    //  *
    //  * @return string
    //  */
    // public function getPlaceholder()
    // {
    //     return $this->getConfig(self::PLACEHOLDER);
    // }
    /**
     *
     * @return boolean
     */
    public function isHighligth()
    {
        return $this->isSetFlag(self::CONFIG_PATH_HIGHLIGHT);
    }

    /**
     *
     * @return boolean
     */
    public function isHint()
    {
        return $this->isSetFlag(self::CONFIG_PATH_HINT);
    }

    /**
     *
     * @return boolean
     */
    public function useQraphQl()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_GRAPHQL) && $this->isEnabled();
    }

    /**
     *
     * @return string [json]
     */
    public function getClassNames()
    {
        $classNames = $this->getConfig(self::CONFIG_PATH_CLASSNAMES);
        if (isset($classNames['dataset'])) {
            $classNames['dataset'] .= ' ' . $this->getResultsLayout();
        }

        return $classNames;
    }

    /**
     * @return string
     */
    public function getResultsLayout()
    {
        return (string) $this->getConfig(self::CONFIG_PATH_RESULTS_LAYOUT);
    }

    /**
     * @return int
     */
    public function getFormLayout()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_FORM_LAYOUT);
    }

    /**
     * Retrieve folded design flag
     *
     * @return boolean
     */
    public function isFoldedDesignEnabled()
    {
        return in_array($this->getFormLayout(), [
            FormLayout::FOLDED_FULLSCREEN,
            FormLayout::FOLDED_INLINE,
        ]);
    }

    /**
     * @return boolean
     */
    public function isFullscreenLayoutEnabled()
    {
        return $this->getFormLayout() == FormLayout::FOLDED_FULLSCREEN;
    }

    /**
     * Retrieve ajaxsearch block additional css classes
     *
     * @return string
     */
    public function getAdditionalCssClass()
    {
        $classes = [];

        if ($this->isFoldedDesignEnabled()) {
            $classes[] = 'folded';

            if ($this->isFullscreenLayoutEnabled()) {
                $classes[] = 'fullscreen';
            } else {
                $classes[] = 'inline';
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Retrieve autocomplete enable
     *
     * @return boolean
     */
    public function isAutocompleteEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_AUTOCOMPLETE_ENABLE) || !$this->isEnabled();
    }

    /**
     * Get Autocomplete limit
     *
     * @return int
     */
    public function getAutocompleteLimit()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_AUTOCOMPLETE_LIMIT);
    }

    /**
     * Retrieve product enable
     *
     * @return boolean
     */
    public function isProductEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_PRODUCT_ENABLE) && $this->isEnabled();
    }

    /**
     * Get Product limit
     *
     * @return int
     */
    public function getProductLimit()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_PRODUCT_LIMIT);
    }

    /**
     * Retrieve is product view all enable
     *
     * @return boolean
     */
    public function isProductViewAllEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_PRODUCT_VIEW_ALL) && $this->isEnabled();
    }

    /**
     * Retrieve category enable
     *
     * @return boolean
     */
    public function isCategoryEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_CATEGORY_ENABLE) && $this->isEnabled();
    }

    /**
     * Retrieve category filter enable
     *
     * @return boolean
     */
    public function isCategoryFilterEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_CATEGORY_FILTER_ENABLE);
    }

    /**
     * Get category limit
     *
     * @return int
     */
    public function getCategoryLimit()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_CATEGORY_LIMIT);
    }

    /**
     * Get category depth
     *
     * @return int
     */
    public function getCategoryDepth()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_CATEGORY_DEPTH);
    }

    /**
     * Retrieve category options load by graphql
     *
     * @return boolean
     */
    public function isCategoryFilterLoadOptionsByGraphql()
    {
        return $this->isSetFlag(self::CONFIG_PATH_CATEGORY_GRAPHQL)
            && $this->isEnabled()
            && $this->isCategoryFilterEnabled();
    }

    /**
     * Retrieve cms page search enable
     *
     * @return boolean
     */
    public function isPageEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_PAGE_ENABLE) && $this->isEnabled();
    }

    /**
     * Get cms page search limit
     *
     * @return int
     */
    public function getPageLimit()
    {
        return (int) $this->getConfig(self::CONFIG_PATH_PAGE_LIMIT);
    }

    /**
     *
     * @return string
     */
    public function getCategoryVarName()
    {
        return \Swissup\Ajaxsearch\Model\QueryFactory::CATEGORY_VAR_NAME;
    }

    /**
     * @return boolean
     */
    public function isPopularEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_AUTOCOMPLETE_ON_FOCUS)
            && $this->isSetFlag(self::CONFIG_PATH_AUTOCOMPLETE_ENABLE)
            && $this->isEnabled();
    }

    /**
     * Retrieve minimum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMinQueryLength($store = null)
    {
        if ($this->isPopularEnabled()) {
            return 0;
        }
        $minQueryLength = parent::getMinQueryLength($store);

        return $minQueryLength ? $minQueryLength : 3;
    }

    /**
     *
     * @return string
     */
    public function getWildcard()
    {
        return '_QUERY';
    }

    /**
     *
     * @param   string $query
     * @return  string
     */
    public function getAjaxActionUrl($query = null)
    {
        return $this->_getUrl(
            'search/ajax/suggest',
            [
                '_query' => [
                    QueryFactory::QUERY_VAR_NAME => $query
                ],
                '_secure' => $this->_request->isSecure()
            ]
        );
    }

    /**
     *
     * @param   string $query
     * @return  string
     */
    public function getGraphQlUrl($query = null)
    {
        return $this->_getUrl(
            'graphql',
            ['_secure' => $this->_request->isSecure()]
        );
    }

    /**
     *
     * @param  mixed  $store
     * @return boolean
     */
    public function isTaxIncludingToPrice($store = null)
    {
        $value = $this->getConfig(self::CONFIG_PATH_TAX_DISPLAY_TYPE, $store);
        return in_array($value, [\Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX]);
    }
}
