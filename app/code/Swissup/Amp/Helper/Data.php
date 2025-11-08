<?php
namespace Swissup\Amp\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path to store config is AMP enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'swissup_amp/general/enabled';

    /**
     * Path to store config is persistent browsing enabled
     *
     * @var string
     */
    const XML_PATH_PERSISTENT_BROWSING = 'swissup_amp/general/persistent_browsing';

    /**
     * Path to store config is AMP enabled for all supported pages
     *
     * @var string
     */
    const XML_PATH_ALL_PAGES = 'swissup_amp/general/all_pages';

    /**
     * Path to store config use AMP on selected pages
     *
     * @var string
     */
    const XML_PATH_PAGES = 'swissup_amp/general/pages';

    /**
     * Path to store config exclude URLs from AMP
     *
     * @var string
     */
    const XML_PATH_EXCLUDE = 'swissup_amp/general/exclude';

    /**
     * Path to store config enable AMP cookie restriction
     *
     * @var string
     */
    const XML_PATH_COOKIE_RESTRICTION = 'swissup_amp/web/cookie_restriction';

    /**
     * Path to store config is add to cart enabled for all supported products
     *
     * @var string
     */
    const XML_PATH_PRODUCT_FULL_MODE = 'swissup_amp/product_page/full_mode';

    /**
     * Path to store config show add to cart for selected product types
     *
     * @var string
     */
    const XML_PATH_PRODUCT_TYPES = 'swissup_amp/product_page/product_types';

    /**
     * Path to store config is layered navigation disabled
     *
     * @var string
     */
    const XML_PATH_LAYERED_NAV_DISABLE = 'swissup_amp/category_page/disable_layered_navigation';

    /**
     * Path to store config AMP homepage identifier
     *
     * @var string
     */
    const XML_PATH_HOME_PAGE_ID = 'swissup_amp/homepage/page_id';

    /**
     * Path to store config redirect to cart page after product was added
     *
     * @var string
     */
    const XML_PATH_REDIRECT_TO_CART = 'checkout/cart/redirect_to_cart';

    /**
     * Path to store config for Custom theme CSS
     *
     * @var string
     */
    const XML_PATH_CUSTOM_CSS = 'swissup_amp/customization/custom_css';

    /**
     * Can use amp flag
     * @var boolean|null
     */
    protected $canUseAmp = null;

    /**
     * Is current page supported by amp
     * @var boolean|null
     */
    protected $isPageSupported = null;

    /**
     * @var \Swissup\Amp\Model\System\Config\Source\Pages
     */
    protected $pagesConfigModel;

    /**
     * @var \Swissup\Amp\Helper\Device
     */
    protected $deviceHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Swissup\Amp\Model\System\Config\Source\Pages $pagesConfigModel
     * @param \Swissup\Amp\Helper\Device $deviceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Swissup\Amp\Model\System\Config\Source\Pages $pagesConfigModel,
        \Swissup\Amp\Helper\Device $deviceHelper
    ) {
        $this->pagesConfigModel = $pagesConfigModel;
        $this->deviceHelper = $deviceHelper;
        parent::__construct($context);
    }

    /**
     * Get config value by key
     * @param  string $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if amp module enabled
     * @return boolean
     */
    public function isAmpEnabled()
    {
        return (bool)$this->getConfig(self::XML_PATH_ENABLED);
    }

    /**
     * Check if persistent browsing should be used
     * @return boolean
     */
    public function isPersistentBrowsingEnabled()
    {
        return (bool)$this->getConfig(self::XML_PATH_PERSISTENT_BROWSING);
    }

    /**
     * Check if add to cart enabled for all products
     * @return boolean
     */
    public function addToCartFullModeEnabled()
    {
        return (bool)$this->getConfig(self::XML_PATH_PRODUCT_FULL_MODE);
    }

    /**
     * Get list of product types to show add to cart
     * @return string
     */
    public function getSelectedProductTypes()
    {
        return (string)$this->getConfig(self::XML_PATH_PRODUCT_TYPES);
    }

    /**
     * Check if layered navigation is disabled
     * @return boolean
     */
    public function disableLayeredNavigation()
    {
        return (bool)$this->getConfig(self::XML_PATH_LAYERED_NAV_DISABLE);
    }

    /**
     * Get amp homepage identifier
     * @return string
     */
    public function getHomepageId()
    {
        return $this->getConfig(self::XML_PATH_HOME_PAGE_ID);
    }

    /**
     * Check if should redirect to cart page after product was added to cart
     * @return boolean
     */
    public function shouldRedirectToCart()
    {
        return (bool)$this->getConfig(self::XML_PATH_REDIRECT_TO_CART);
    }

    /**
     * Check if cookie restriction is enabled
     * @return boolean
     */
    public function cookieRestriction()
    {
        return (bool)$this->getConfig(self::XML_PATH_COOKIE_RESTRICTION);
    }

    /**
     * Checks if AMP can be used at current page
     *
     * This check is used before AMP activation.
     *
     * @return boolean
     */
    public function canUseAmp()
    {
        if ($this->isEmptyFullActionName()) return false;

        if (null === $this->canUseAmp) {
            $flag = $this->_getRequest()->getParam('amp');
            $this->canUseAmp = (bool)$flag;

            if (!$this->isAmpEnabled() || !$this->isPageSupported()) {
                $this->canUseAmp = false;
            } elseif ($this->isAmpForced()
                && null === $flag
                && !$this->_getRequest()->isAjax()) {

                // force amp theme for all non ajax requests if flag is not sent
                $this->canUseAmp = true;
            }
        }

        return $this->canUseAmp;
    }

    /**
     * Check if AMP should be forced on a current device
     *
     * @param  string  $device Device type to check [mobile|tablet]
     * @return boolean
     */
    public function isAmpForced($device = null)
    {
        // can't use force, when persistent_browsing is disabled
        if (!$this->isPersistentBrowsingEnabled()) {
            return false;
        }

        if (null === $device) {
            $device = $this->deviceHelper->getDeviceType();
        }

        return $this->getConfig('swissup_amp/general/force_' . $device);
    }

    /**
     * Check if AMP is supported at received page
     *
     * @param  $page    module_controller_action
     * @return boolean
     */
    public function isPageSupported($page = null)
    {
        if ($this->isUrlExcluded() || $this->isEmptyFullActionName()) return false;

        if (null === $this->isPageSupported) {
            if (null === $page) {
                $page = $this->_request->getFullActionName();
            }

            $supportedPages = $this->getSupportedPages();
            if ((strpos($page, 'cms_index_') === 0 ||
                $page == '_noroute_index') && // Noroute controller
                in_array('cms_index_index', $supportedPages)) {

                // defaultIndexAction, defaultNoRoute, noCookiesAction support
                return true;
            }

            $object = new \Magento\Framework\DataObject([
                'current_page' => $page,
                'supported_pages' => $supportedPages,
                'is_page_supported' => false,
            ]);

            $this->_eventManager->dispatch(
                'swissupamp_is_page_supported',
                ['result' => $object]
            );

            if ($object->getIsPageSupported()) {
                return true;
            }

            $this->isPageSupported = in_array($page, $object->getSupportedPages());
        }

        return $this->isPageSupported;
    }

    /**
     * Check if URL is excluded from AMP
     *
     * @return boolean
     */
    public function isUrlExcluded()
    {
        $excludedUrls = (string) $this->getConfig(self::XML_PATH_EXCLUDE);

        if (empty(trim($excludedUrls))) {
            return false;
        }

        $excludedUrls = explode(PHP_EOL, $excludedUrls);
        $requestString = rtrim($this->_request->getRequestString(), '?amp=1');

        foreach ($excludedUrls as $url) {
            $url = trim($url);
            if (trim($url, '/') === trim($requestString, '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the list of supported pages.
     *
     * @return array
     */
    public function getSupportedPages()
    {
        if ($this->getConfig(self::XML_PATH_ALL_PAGES)) {
            $pages = array_keys($this->pagesConfigModel->toArray());
        } else {
            $pages = (string) $this->getConfig(self::XML_PATH_PAGES);
            $pages = array_filter(explode(',', $pages));
        }

        $pages[] = 'checkout_cart_add'; // fix to generate amp=1 urls, when adding product to the cart
        $pages[] = 'swissupamp_cart_add'; // fix to generate amp=1 urls, when adding product to the cart
        $pages[] = 'catalog_product_compare_index';
        $pages[] = 'contact_index_post';

        return $pages;
    }

    /**
     * Check if string ends with a substring
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public function endsWith($haystack, $needle)
    {
        $temp = strlen($haystack) - strlen($needle);

        return $needle === '' || ($temp >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * Get class name and remove Interceptor if exists
     * @param  mixed $object
     * @return string
     */
    public function getCleanClass($object)
    {
        $class = get_class($object);
        if ($this->endsWith($class, '\Interceptor')) {
            $class = preg_replace('/\\\\Interceptor$/', '', $class);
        }

        return $class;
    }

    /**
     * Get amp url for current page
     *
     * @return string
     */
    public function getAmpUrl()
    {
        $url = $this->_urlBuilder->getCurrentUrl();

        // inject `amp=1` into query params
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $url = substr_replace($url, '?amp=1&', $pos, 1);
        } else {
            $url .= '?amp=1';
        }

        // Fix possible duplicates in 'amphtml' url
        $url = str_replace(['&amp=0', '&amp=1'], '', $url);

        return $url;
    }

    /**
     * Check if we can skip form key validation for some actions.
     * This feature is used to fix some actions on google cached pages:
     *
     *  - add to cart
     *  - add to compare
     *
     * @return boolean
     */
    public function canSkipFormKeyValidation()
    {
        if (!$this->isAmpEnabled()) {
            return false;
        }

        $actions = [
            'checkout_cart_add',
            'catalog_product_compare_add',
            'catalog_product_compare_remove',
            'wishlist_index_add',
            'contact_index_post'
        ];
        $currentAction = $this->_getRequest()->getFullActionName();

        // fix cookie allow
        if ($currentAction == 'swissupamp_cookie_allow') return true;

        return in_array($currentAction, $actions) &&
            $this->isPageServedFromAmpCache();
    }

    /**
     * Check if request is served from google cache
     *
     * @return boolean
     */
    public function isPageServedFromAmpCache()
    {
        if ($this->_getRequest()->getHeader('AMP-Same-Origin') === 'true') {
            return true;
        }

        if ($origin = $this->_getRequest()->getHeader('Origin')) {
            return $origin === $this->getAmpCacheDomainName();
        }

        if ($referer = $this->_getRequest()->getHeader('Referer')) {
            return strpos($referer, $this->getAmpCacheDomainName()) === 0;
        }

        return false;
    }

    /**
     * Retrieve domain name for page, served from google cache
     *
     * See https://developers.google.com/amp/cache/overview#amp-cache-url-format
     * for more information
     *
     * @return string
     */
    public function getAmpCacheDomainName()
    {
        $domain = $this->_getRequest()->getHttpHost();
        $domain = str_replace('-', '--', $domain);
        $domain = str_replace('.', '-', $domain);

        return "https://{$domain}.cdn.ampproject.org";
    }

    /**
     * Prepare html for DOMDocument usage
     * @param  string $html
     * @return string
     */
    public function prepareDOMDocumentHtml($html)
    {
        // fix special characters
        // https://php.watch/versions/8.2/mbstring-qprint-base64-uuencode-html-entities-deprecated#html
        $html = htmlentities($html);
        $html = htmlspecialchars_decode($html);

        // escape too early close tag inner script : <script>alert("</div>")</script>
        // https://stackoverflow.com/questions/236073/why-split-the-script-tag-when-writing-it-with-document-write/236106#236106
        $regExp = '/<script\b[^>]*>(.*?)<\/script>/is';
        $matches = [];
        preg_match_all($regExp, $html, $matches);
        foreach ($matches[1] as $_script) {
            if (strstr($_script, '</')) {
                $html = str_replace($_script, str_replace('</', '<\/', $_script), $html);
            }
        }

        return $html;
    }

    /**
     * Check if we are on product page
     * @return boolean
     */
    public function isProductPage()
    {
        return $this->_request->getFullActionName() == 'catalog_product_view';
    }

    /**
     * Get product formatted price (2.1 compatibility)
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getFormattedPrice($product)
    {
        if (method_exists($product, 'getFormattedPrice')) {
            return strip_tags($product->getFormattedPrice());
        } else {
            return strip_tags($product->getFormatedPrice());
        }
    }

    /**
     * @see https://amp.dev/documentation/guides-and-tutorials/learn/amp-caches-and-cors/amp-cors-requests/#cors-sample-code
     *
     * @param  \Magento\Framework\App\RequestInterface $request
     * @return string|bool
     */
    public function validateCorsRequest(
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$request->isGet() && !$request->isPost()) {
            return false;
        }

        $sourceOrigin = $request->getScheme() . '://' . $request->getHttpHost();
        $allowedOrigins = [
            $sourceOrigin,
            $this->getAmpCacheDomainName()
        ];

        if ($origin = $request->getHeader('Origin')) {
            if (!in_array($origin, $allowedOrigins)) {
                return false;
            }
        } else if ($request->getHeader('AMP-Same-Origin') === 'true') {
            $origin = $sourceOrigin;
        } else {
            return false;
        }

        return $origin;
    }

    /**
     * Prepare unauthorized response
     * @param  \Magento\Framework\App\ResponseInterface $response
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function unauthorizedResponse(
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $result['message'] = __('Unauthorized Request');

        return $response->setHttpResponseCode(403)
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($result));
    }

    /**
     * Prepare successful response
     * @param  \Magento\Framework\App\ResponseInterface $response
     * @param  string $origin
     * @param  array $result
     * @param  int $code
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function successfulResponse(
        \Magento\Framework\App\ResponseInterface $response, $origin, $result, $code = 200
    ) {
        return $response
            ->clearHeader('Location')
            ->setHttpResponseCode($code)
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Access-Control-Allow-Origin', $origin)
            ->setHeader('Access-Control-Expose-Headers', 'AMP-Redirect-To')
            ->setBody(json_encode($result));
    }

    /**
     * Get redirect to URL from response location header
     * @param  \Magento\Framework\App\ResponseInterface $response
     * @return string|bool
     */
    public function getRedirectTo(
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $redirectTo = false;
        foreach ($response->getHeaders() as $header) {
            if (is_array($header) && $header['name'] === 'Location') {
                $redirectTo = $header['value'];
                break;
            } else if (method_exists($header, 'getFieldName') &&
                $header->getFieldName() === 'Location'
            ) {
                $redirectTo = $header->getFieldValue();
                break;
            }
        }
        if ($redirectTo) {
            $redirectTo = str_replace('http://', 'https://', $redirectTo);
        }

        return $redirectTo;
    }

    /**
     * Retrieve shopping cart url
     *
     * @return string
     */
    public function getCartUrl()
    {
        return $this->_getUrl('checkout/cart', ['_forced_secure' => true]);
    }

    /**
     * Check if request is Varnish ESI blocks request
     * @return boolean
     */
    public function isVarnishRequest()
    {
        return $this->isAmpEnabled() &&
            strpos($this->_request->getPathInfo(), '/page_cache/block/esi') === 0 &&
            $this->_getRequest()->getParam('amp');
    }

    /**
     * Check if full action name is empty
     * @return boolean
     */
    protected function isEmptyFullActionName()
    {
        return $this->_request->getFullActionName() == '__';
    }

    /**
     * Get amp custom CSS
     * @return string
     */
    public function getCustomCss()
    {
        return $this->getConfig(self::XML_PATH_CUSTOM_CSS);
    }

}
