<?php
namespace Swissup\Pagespeed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Detection\MobileDetect;
use Swissup\Pagespeed\Model\Config\Frontend\Pages;

class Config extends AbstractHelper
{
    const CONFIG_XML_PATH_ENABLE               = 'pagespeed/main/enable';
    const CONFIG_XML_PATH_DEV_MODE             = 'pagespeed/main/devmode';
    const CONFIG_XML_PATH_DEBUG_MODE           = 'pagespeed/main/debug_mode';
    const CONFIG_XML_PATH_AFTER_RENDER         = 'pagespeed/main/after_render';
    const CONFIG_XML_PATH_ENABLE_FOR_ALL_PAGES = 'pagespeed/main/enable_for_all_pages';
    const CONFIG_XML_PATH_ENABLE_FOR_PAGES     = 'pagespeed/main/enable_for_pages';
    const CONFIG_XML_PATH_LINK_PRELOAD_ENABLED = 'pagespeed/main/link_preload';
    const CONFIG_XML_PATH_CUSTOM_PRELOAD_LINK  = 'pagespeed/main/custom_preload_link';
    const CONFIG_XML_PATH_CONTENT_ENABLE     = 'pagespeed/content/enable';
    const CONFIG_XML_PATH_CONTENT_JS         = 'pagespeed/content/js';
    const CONFIG_XML_PATH_CONTENT_CSS        = 'pagespeed/content/css';
    const CONFIG_XML_PATH_JS_MERGE           = 'dev/js/merge_files';
    const CONFIG_XML_PATH_JS_ENABLE_JS_BUNDLING = 'dev/js/enable_js_bundling';
    const CONFIG_XML_PATH_JS_ENABLE_ADVANCED_JS_BUNDLING = 'pagespeed/js/enable_advanced_js_bundling';
    const CONFIG_XML_PATH_JS_RJS_BUILD_CONFIG = 'pagespeed/js/rjs_build_config';
    const CONFIG_XML_PATH_JS_DEFER           = 'pagespeed/js/defer_enable';
    // const CONFIG_XML_PATH_JS_MOVE_INLINE_TO_BOTTOM = 'dev/js/move_script_to_bottom';
    const CONFIG_XML_PATH_JS_DEFER_UNPACK    = 'pagespeed/js/defer_unpack';
    const CONFIG_XML_PATH_JS_DEFER_UNPACK_INTERACTIVE = 'pagespeed/js/defer_unpack_interactive';
    const CONFIG_XML_PATH_JS_DEFER_IGNORE    = 'pagespeed/js/defer_ignore';
    const CONFIG_XML_PATH_CSS_MERGE          = 'dev/css/merge_css_files';
    const CONFIG_XML_PATH_USE_CSS_CRITICAL_PATH = 'dev/css/use_css_critical_path';
    const CONFIG_XML_PATH_CSS_CRITICAL_ENABLE = 'pagespeed/css/critical_enable';
    const CONFIG_XML_PATH_CSS_CRITICAL_HANDLES = 'pagespeed/css/critical_handles';
    const CONFIG_XML_PATH_CSS_CRITICAL_DEFAULT = 'pagespeed/css/critical_default';
    const CONFIG_XML_PATH_CSS_CRITICAL_LAYOUT = 'pagespeed/css/critical_layout';
    const CONFIG_XML_PATH_EXPIRE_ENABLE      = 'pagespeed/expire/enable';
    const CONFIG_XML_PATH_EXPIRE_TTL         = 'pagespeed/expire/ttl';
    const CONFIG_XML_PATH_DNSPREFETCH_ENABLE = 'pagespeed/dnsprefetch/enable';
    const CONFIG_XML_PATH_PRECONNECT_ENABLE = 'pagespeed/preconnect/enable';

    const CONFIG_XML_PATH_IMAGE_OPTIMISE_WEBP_PICTURE_ADD = 'pagespeed/image/optimize_webp_picture_add';
    const CONFIG_XML_PATH_IMAGE_LAZYLOAD_ENABLE = 'pagespeed/image/lazyload_enable';
    const CONFIG_XML_PATH_IMAGE_LAZYLOAD_IGNORE = 'pagespeed/image/lazyload_ignore';
    const CONFIG_XML_PATH_IMAGE_LAZYLOAD_OFFSET = 'pagespeed/image/lazyload_offset';
    const CONFIG_XML_PATH_IMAGE_LAZYLOAD_MOBILE_OFFSET = 'pagespeed/image/lazyload_mobile_offset';
    const CONFIG_XML_PATH_IMAGE_DIMENSION  = 'pagespeed/image/dimension';
    const CONFIG_XML_PATH_IMAGE_RESPONSIVE_ENABLE = 'pagespeed/image/responsive';
    const CONFIG_XML_PATH_IMAGE_RESPONSIVE_SIZES = 'pagespeed/image/default_responsive_sizes';

    /**
     * @var string
     */
    private $stateMode;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectoryWriter;

    /**
     *
     * @var MobileDetect
     */
    private $detector;

    /**
     *
     * @var \Swissup\Pagespeed\Model\Config\Backend\File\RjsFactory
     */
    private $rjsConfigFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;

    /**
     * @var \Swissup\ImageOptimizer\Helper\Config
     */
    private $imageOptimizerConfigHelper;

    /**
     * @var \Swissup\Pagespeed\Model\Config\Frontend\PagesFactory
     */
    private $pagesFactory;

    /**
     * @var boolean
     */
    private $isEnabled;

    /**
     * @var boolean
     */
    private $isEnableOnPage;

    /**
     * @var boolean
     */
    private $statusInDebugMode;

    /**
     * @var boolean
     */
    private $isAllowedCriticalCssOnCurrentPage;

    /**
     * @var array|null
     */
    private $allPageHandles;

    /**
     * @param Context $context
     * @param AppState $state
     * @param Filesystem $filesystem
     * @param MobileDetect $detector
     * @param \Swissup\Pagespeed\Model\Config\Backend\File\RjsFactory $rjsConfigFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Swissup\ImageOptimizer\Helper\Config $imageOptimizerConfigHelper
     * @param \Swissup\Pagespeed\Model\Config\Frontend\PagesFactory $pagesFactory
     */
    public function __construct(
        Context $context,
        AppState $state,
        Filesystem $filesystem,
        MobileDetect $detector,
        \Swissup\Pagespeed\Model\Config\Backend\File\RjsFactory $rjsConfigFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Swissup\ImageOptimizer\Helper\Config $imageOptimizerConfigHelper,
        \Swissup\Pagespeed\Model\Config\Frontend\PagesFactory $pagesFactory
    ) {
        parent::__construct($context);
        $this->stateMode = $state->getMode();
        $this->mediaDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->detector = $detector;
        $this->rjsConfigFactory = $rjsConfigFactory;
        $this->serializer = $serializer;
        $this->imageOptimizerConfigHelper = $imageOptimizerConfigHelper;
        $this->pagesFactory = $pagesFactory;
    }

    /**
     *
     * @param  string $key
     * @return mixed
     */
    private function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @param  string $key
     * @return boolean
     */
    private function isSetFlag($key)
    {
        return $this->scopeConfig->isSetFlag($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @return boolean
     */
    public function isDeveloperMode()
    {
        return $this->stateMode === AppState::MODE_DEVELOPER;
    }

    /**
     *
     * @return boolean
     */
    public function isDeveloperModeIgnored()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_DEV_MODE);
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnable();
    }

    public function isEnable()
    {
        if ($this->isEnabled === null) {
            $this->isEnabled = $this->isSetFlag(self::CONFIG_XML_PATH_ENABLE)
                && $this->isEnableOnPage()
                && ($this->isDebugModeEnabled() ? $this->getStatusInDebugMode() : true)
                && (!$this->isDeveloperMode() || $this->isDeveloperModeIgnored());
        }

        return $this->isEnabled;
    }

    private function isDebugModeEnabled()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_DEBUG_MODE);
    }

    private function getStatusInDebugMode()
    {
        if ($this->statusInDebugMode === null) {
            $this->statusInDebugMode = (bool) $this->_request->getParam('pagespeed', true);
        }

        return $this->statusInDebugMode;
    }

    /**
     * @return array|int[]|string[]|null
     */
    private function getAllPageHandles()
    {
        if ($this->allPageHandles === null) {
            $this->allPageHandles = array_keys($this->pagesFactory->create()->toArray());
        }

        return $this->allPageHandles;
    }

    /**
     *
     * @return boolean
     */
    public function isEnableOnPage()
    {
        if ($this->isSetFlag(self::CONFIG_XML_PATH_ENABLE_FOR_ALL_PAGES)) {
            return true;
        }
        if ($this->isEnableOnPage === null) {
            $allowedPages = (string) $this->getConfig(self::CONFIG_XML_PATH_ENABLE_FOR_PAGES);
            $allowedPages = str_replace(["\r", ' ', "\n"], '', $allowedPages);
            $allowedPages = explode(',', $allowedPages);
            $allowedPages = array_filter($allowedPages);
            $allPages = $this->getAllPageHandles();
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->_getRequest();
            $handle = implode('_', [
                $request->getModuleName(),
                $request->getControllerName(),
                $request->getActionName()
            ]);
            $page = in_array($handle, $allPages) ? $handle : Pages::ALL_ANOTHER_PAGES;

            $this->isEnableOnPage = in_array($page, $allowedPages);
        }
        return $this->isEnableOnPage;
    }

    /**
     * @see \Swissup\Pagespeed\Plugin\Controller\Result\AfterRenderResultPlugin::afterRenderResult
     * @return bool
     */
    public function isEnableDynamicHtmlProcessing()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_AFTER_RENDER);
    }

    /**
     *
     * @return boolean
     */
    public function isContentMinifyEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_CONTENT_ENABLE);
    }

    /**
     *
     * @return boolean
     */
    public function isContentJsMinifyEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_CONTENT_JS);
    }

    /**
     *
     * @return boolean
     */
    public function isContentCssMinifyEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_CONTENT_CSS);
    }

    /**
     *
     * @return boolean
     */
    public function isAddExpireEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_EXPIRE_ENABLE);
    }

    /**
     *
     * @return int
     */
    public function getExpireTTL()
    {
        return (int) $this->getConfig(self::CONFIG_XML_PATH_EXPIRE_TTL);
    }

    /**
     *
     * @return boolean
     */
    public function isDnsPrefetchEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_DNSPREFETCH_ENABLE);
    }

    /**
     *
     * @return boolean
     */
    public function isPreconnectEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_PRECONNECT_ENABLE);
    }

    /**
     *
     * @return boolean
     */
    public function isJsMergeEnable()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_JS_MERGE);
    }

    /**
     *
     * @return boolean
     */
    public function isDeferJsEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_JS_DEFER);
    }

    /**
     *
     * @return boolean
     */
    public function isDeferJsUnpackEnable()
    {
        return $this->isDeferJsEnable() && $this->isSetFlag(self::CONFIG_XML_PATH_JS_DEFER_UNPACK);
    }

    /**
     *
     * @return boolean
     */
    public function isInteractiveDeferEnable()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->_getRequest();
        $handle = $request->getFullActionName();
        $disallowedHandles = [
            'checkout_index_index',
            'firecheckout_index_index'
        ];
        $isIgnoredPage = in_array($handle, $disallowedHandles);

        return $this->isEnabled()
            && $this->isDeferJsEnable()
            && $this->isDeferJsUnpackEnable()
            && $this->isSetFlag(self::CONFIG_XML_PATH_JS_DEFER_UNPACK_INTERACTIVE)
            && !$isIgnoredPage
        ;
    }

    /**
     *
     * @return string
     */
    public function getDelayScriptType()
    {
        return 'text/defer-javascript';
    }

    /**
     *
     * @return array
     */
    public function getDeferJsIgnores()
    {
        $ignores = explode("\n", (string) $this->getConfig(self::CONFIG_XML_PATH_JS_DEFER_IGNORE));
        foreach ($ignores as &$ignore) {
            $ignore = trim($ignore, " \r");
        }
        $ignores = array_filter($ignores);
        $ignores = array_unique($ignores);
        return $ignores;
    }

    /**
     * @return bool
     */
    public function isForceRequireJsLoadingEnabled()
    {
        return false && $this->isEnabled();
    }

    /**
     *
     * @return boolean
     */
    public function isMergeJsFilesForMobileDisabled()
    {
        return false;
//        return false && $this->detector->isMobile();
    }

    /**
     *
     * @return boolean
     */
    public function isMobile()
    {
        return $this->detector->isMobile();
    }

    /**
     *
     * @return boolean
     */
    public function isMergeCssFilesForMobileDisabled()
    {
        return false;
//        return false && $this->isMobile();
    }

    /**
     *
     * @return boolean
     */
    public function isAutoAddFontDisplayForMergedCss()
    {
        return $this->isEnabled();
        // return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_);
    }

    /**
     *
     * @return boolean
     */
    public function isImageLazyLoadEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_LAZYLOAD_ENABLE);
    }

    /**
     *
     * @return array
     */
    public function getLazyloadIgnores()
    {
        $ignores = explode("\n", (string) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_LAZYLOAD_IGNORE));
        foreach ($ignores as &$ignore) {
            $ignore = trim($ignore, " \r");
        }
        $ignores = array_filter($ignores);
        return $ignores;
    }

    /**
     *
     * @return int
     */
    public function getLazyloadOffset()
    {
        return  (int) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_LAZYLOAD_OFFSET);
    }

    /**
     *
     * @return int
     */
    public function getLazyloadMobileOffset()
    {
        return  (int) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_LAZYLOAD_MOBILE_OFFSET);
    }

    /**
     *
     * @return boolean
     */
    public function isUseCssCriticalPathEnable()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_USE_CSS_CRITICAL_PATH);
        //&& $this->isAllowedCriticalCssOnCurrentPage() // call Swissup\Pagespeed\Plugin\Framework\App\ConfigPlugin
    }

    /**
     *
     * @return boolean
     */
    public function isCriticalCssEnable()
    {
        return $this->isEnabled()
            && $this->isSetFlag(self::CONFIG_XML_PATH_CSS_CRITICAL_ENABLE)
            && $this->isAllowedCriticalCssOnCurrentPage();
    }

    /**
     *
     * @return bool
     */
    public function isAllowedCriticalCssOnCurrentPage()
    {
        if ($this->isAllowedCriticalCssOnCurrentPage === null) {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->_getRequest();
            $handle = $request->getFullActionName();
            $allowedPages = (string) $this->getConfig(self::CONFIG_XML_PATH_CSS_CRITICAL_HANDLES);
            $allowedPages = str_replace(["\r", ' ', "\n"], '', $allowedPages);
            $allowedPages = explode(',', $allowedPages);
            $allowedPages = array_filter($allowedPages);
            $allPages = $this->getAllPageHandles();
            $page = in_array($handle, $allPages) ? $handle : Pages::ALL_ANOTHER_PAGES;
            $this->isAllowedCriticalCssOnCurrentPage = in_array($page, $allowedPages);
        }
        return $this->isAllowedCriticalCssOnCurrentPage;
    }

    /**
     *
     * @return boolean
     */
    public function isCriticalCssThemeHanleMergeEnable()
    {
        return $this->isEnabled()
            && $this->isUseCssCriticalPathEnable()
            && $this->isSetFlag(self::CONFIG_XML_PATH_CSS_CRITICAL_LAYOUT);
    }

    /**
     *
     * @return string
     */
    public function getDefaultCriticalCss()
    {
        $value = '';
        $filename = (string) $this->getConfig(self::CONFIG_XML_PATH_CSS_CRITICAL_DEFAULT);
        $writer = $this->mediaDirectoryWriter;

        if ($writer->isExist($filename) &&
            $writer->isFile($filename) &&
            $writer->isReadable($filename)
        ) {
            $_value = $writer->readFile($filename);
            if (!empty($_value)) {
                $value = $_value;
            }
        }

        return trim($value);
    }

    /**
     *
     * @return boolean
     */
    public function isMergeCssEnable()
    {
        return $this->isSetFlag(self::CONFIG_XML_PATH_CSS_MERGE);
    }

    /**
     *
     * @return boolean
     */
    public function isDimensionEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_DIMENSION);
    }

    /**
     *
     * @return boolean
     */
    public function isWebPEnable()
    {
        return $this->imageOptimizerConfigHelper->isWebPEnable();
    }

    /**
     *
     * @return boolean
     */
    public function isWebPSupport()
    {
        $detector = $this->detector;

        return $this->isWebPEnable()
            && ($detector->is('Chrome')
                || $detector->is('Opera')
                || $detector->isAndroidOS()
                || (int) $detector->version('Firefox') >= 65
                || (int) $detector->version('Chrome') > 32
            );
    }

    /**
     *
     * @return boolean
     */
    public function isWebPAddPictureTag()
    {
        return $this->isWebPEnable()
            && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_WEBP_PICTURE_ADD);
    }

    /**
     *
     * @return boolean
     */
    public function isReplaceWebPInJs()
    {
        return $this->isWebPEnable();
    }

    /**
     *
     * @return boolean
     */
    public function isLinkPreloadEnabled()
    {
        return $this->isEnabled()
            && $this->isSetFlag(self::CONFIG_XML_PATH_LINK_PRELOAD_ENABLED);
    }

    /**
     *
     * @return array
     */
    public function getCustomLinkForPreload()
    {
        $links = (string) $this->getConfig(self::CONFIG_XML_PATH_CUSTOM_PRELOAD_LINK);
        $links = explode("\n", $links);
        foreach ($links as &$link) {
            $link = trim($link, " \r");
        }
        return $links;
    }

    /**
     *
     * @return boolean
     */
    public function isImageResponsiveEnable()
    {
        return $this->isEnabled() && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_RESPONSIVE_ENABLE);
    }

    /**
     *
     * @return string
     */
    public function getDefaultImageResponsiveSizes()
    {
        return (string) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_RESPONSIVE_SIZES);
    }

    /**
     *
     * @return array of string
     */
    public function getResizeCommandTargetDirs()
    {
        return $this->imageOptimizerConfigHelper->getResizeCommandTargetDirs();
    }

    /**
     *
     * @return boolean
     */
    public function isAdvancedJsBundling()
    {
        return $this->isEnabled()
            && !$this->isSetFlag(self::CONFIG_XML_PATH_JS_ENABLE_JS_BUNDLING)
            && $this->isSetFlag(self::CONFIG_XML_PATH_JS_ENABLE_ADVANCED_JS_BUNDLING);
    }

    /**
     *
     * @return array
     */
    public function getRjsJsonConfig()
    {
        $value = (string) $this->getConfig(self::CONFIG_XML_PATH_JS_RJS_BUILD_CONFIG);

        $model = $this->rjsConfigFactory->create()->setValue($value);
        $model->afterLoad();

        $value = $model->getValue();

        json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $value = [];
        } else {
            $value = $this->serializer->unserialize($value);
        }
        return $value;
    }

    /**
     * @return boolean
     */
    public function isVarnishEnabled()
    {
        return $this->getConfig(\Magento\PageCache\Model\Config::XML_PAGECACHE_TYPE) ===
            (string) \Magento\PageCache\Model\Config::VARNISH;
    }
}
