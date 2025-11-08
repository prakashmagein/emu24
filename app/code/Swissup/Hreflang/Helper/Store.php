<?php

namespace Swissup\Hreflang\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\Hreflang\Model\Config\Source\StoreView;
use Swissup\Hreflang\Model\Config\Source\ValueStrategy;

class Store
{
    const XML_PATH_LOCALE_IN_URL = 'swissup_hreflang/url/add_locale';
    const XML_PATH_REMOVE_REGION = 'swissup_hreflang/url/remove_region';
    const XML_PATH_REMOVE_STORECODE = 'swissup_hreflang/url/remove_store_code';
    const XML_PATH_HREFLANG_IN_PAGE = 'swissup_hreflang/general/enabled';
    const XML_PATH_HREFLANG_IN_XMLSITEMAP = 'swissup_hreflang/general/enabled_xml';
    const XML_PATH_IS_ALL_WEBSITES = 'swissup_hreflang/general/all_websites';
    const XML_PATH_ALLOWED_WEBSITES = 'swissup_hreflang/general/allowed_websites';
    const XML_PATH_EXCLUDE_STORE = 'swissup_hreflang/general/excluded';
    const XML_PATH_VALUE_STRATEGY = 'swissup_hreflang/general/value_strategy';
    const XML_PATH_CUSTOM_VALUE = 'swissup_hreflang/general/hreflang_custom_value';

    protected StoreManagerInterface $storeManager;
    protected ScopeConfigInterface $scopeConfig;
    protected bool $localeInUrlProcessed = false;
    protected ?StoreModel $currentStore;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getLocale(StoreModel$store): string
    {
        return (string)$store->getConfig(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE
        );
    }

    /**
     * Get hreflang for store
     */
    public function getHreflang(StoreModel$store): string
    {
        $locale = $this->getLocale($store);
        $parts = explode('_', $locale);
        if (count($parts)) {
            $parts = array_map('strtolower', $parts);
            $isRemoveRegion = $this->scopeConfig->isSetFlag(
                self::XML_PATH_REMOVE_REGION,
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
            if ($isRemoveRegion) {
                $parts = array_slice($parts, 0, 1);
            }

            return implode('-', $parts);
        }

        return '';
    }

    public function getHreflangAttributeValue(StoreModel$store): array
    {
        $strategy = $store->getConfig(self::XML_PATH_VALUE_STRATEGY);
        if ($strategy == ValueStrategy::CUSTOM_VALUE) {
            return array_map(function ($value) {
                    return trim(strtolower($value));
                },
                explode(',', (string)$store->getConfig(self::XML_PATH_CUSTOM_VALUE))
            );
        }

        return [$this->getHreflang($store)];
    }

    /**
     * Is add locale in url enabled
     */
    public function isLocaleInUrl(StoreModel$store): bool
    {
        return $this->scopeConfig->isSetFlag(
                self::XML_PATH_LOCALE_IN_URL,
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
    }

    public function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }

    /**
     * Is $store Admin
     */
    public function isAdmin(StoreModel $store): bool
    {
        return $store->getCode() == StoreModel::ADMIN_CODE;
    }

    /**
     * Is redirect allowed
     */
    public function isRedirectAllowed(): bool
    {
        return $this->isLocaleInUrl($this->storeManager->getStore())
            && !$this->localeInUrlProcessed;
    }

    /**
     * Set value for flag locale_in_url_processed
     */
    public function setLocaleInUrlProcessed($flag = true)
    {
        $this->localeInUrlProcessed = $flag;
        return $this;
    }

    /**
     * Is add hreflang to page head for $store
     */
    public function isEnabledInPage(StoreModel $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_HREFLANG_IN_PAGE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Is add hreflang to XML Sitemap for $store
     */
    public function isEnabledInXmlSitemap(StoreModel $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_HREFLANG_IN_XMLSITEMAP,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Get x-default store
     */
    public function getXDefaultStore(StoreModel $store): ?StoreModel
    {
        $id = (int)$this->scopeConfig->getValue(
            'swissup_hreflang/general/default_store',
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        return $id === StoreView::NOT_SPECIFIED ?
            null :
            $this->storeManager->getStore($id);
    }

    /**
     * Is $store excluded from hreflang data
     */
    public function isExcluded(StoreModel $store): bool
    {
        // check if store view is disabled
        if (!$store->isActive()) {
            return true;
        }

        // check module config Excluded for this store
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXCLUDE_STORE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Is store_code parameter remove enabled from URL for $store.
     */
    public function isRemoveStorecode(StoreModel $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REMOVE_STORECODE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    public function setCurrentStore(?StoreModel $store = null)
    {
        $this->currentStore = $store;
    }

    public function getCurrentStore(): ?StoreModel
    {
        return $this->currentStore ?? null;
    }

    /**
     * @param  StoreModel                                 $store
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getAllowedWebsites(StoreModel $store): array
    {
        $websites = $this->storeManager->getWebsites();
        $isAll = $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_ALL_WEBSITES,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        if ($isAll) {
            return $websites;
        }

        $allowedIds = (string)$this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_WEBSITES,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $allowedIds = explode(',', $allowedIds);

        foreach ($websites as $key => $website) {
            if (!in_array($website->getId(), $allowedIds)) {
                unset($websites[$key]);
            }
        }

        return $websites;
    }
}
